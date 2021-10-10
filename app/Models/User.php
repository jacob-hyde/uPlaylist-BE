<?php

namespace App\Models;

use Carbon\Carbon;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Faker\Provider\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Cashier\Billable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use KnotAShell\Orders\Models\Customer;

class User extends Authenticatable
{
    use SoftDeletes;
    use HasFactory;
    use Billable;
    use CascadeSoftDeletes;
    use HasApiTokens;

    protected $table = 'users';

    protected $cascadeDeletes = ['curator'];

    protected $fillable = [
        'uuid',
        'api_client_id',
        'external_user_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'paypal_email',
        'stripe_id',
        'card_brand',
        'card_last_four',
        'trial_ends_at',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function (self $user) {
            $user->uuid = Uuid::uuid();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function curator()
    {
        return $this->hasOne(Curator::class);
    }

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function spotify()
    {
        return $this->hasOne(UserSpotify::class);
    }

    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function generateToken()
    {
        $token_result = $this->createToken('Personal Access Token');
        $token        = $token_result->token;
        $token->expires_at = Carbon::now()->addWeeks(2);
        $token->save();

        return $token_result;
    }

    public function getLoginData(bool $with_token = true): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->first_name . ' ' . $this->last_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'email' => $this->email,
            'is_curator' => true,
            'paypal_email' => $this->paypal_email,
            'spotify_connected' => $this->spotify && $this->spotify->access_token ? true : false,
            'price' => $this->curator->price,
            'subscribed' => $this->subscribed('curator'),
            'subscription_id' => $this->subscribed('curator') ? $this->subscription('curator')->id : null,
            'subscription_ends_at' => $this->subscribed('curator') ? $this->subscription('curator')->ends_at : null,
            'payout' => convertCentsToDollars($this->curator->payout_amount),
        ];

        if ($with_token) {
            $token = $this->generateToken();
            $data['token'] = $token->accessToken;
            $data['token_type'] = 'Bearer';
            $data['expires_at'] = Carbon::parse($token->token->expires_at)->toDateTimeString();
        }
        return $data;
    }

    public function getOrderUserLoginData(bool $with_token = true): array
    {
        $data = [
            'name' => $this->first_name . ' ' . $this->last_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
        ];

        if ($with_token) {
            $token = $this->generateToken();
            $data['token'] = $token->accessToken;
            $data['token_type'] = 'Bearer';
            $data['expires_at'] = Carbon::parse($token->token->expires_at)->toDateTimeString();
        }
        return $data;
    }

    /**
     * Generate a username from a email.
     *
     * @param string|null $email
     * @return string
     */
    public static function generateUsername(?string $email = null): string
    {
        $username = preg_replace('/(@.*)$/', '', $email);

        if (!$username) {
            $username = Str::random(8);
        }

        $count    = self::where('username', 'LIKE', $username . '%')->count();
        $username = $username . ($count > 0 ? $count + 1 : '');

        if (self::where('username', $username)->withTrashed()->exists()) {
            $username .= Str::random(5);
        }

        return $username;
    }

    public static function isUsernameAvailable(string $username, self $user = null): bool
    {
        $username = strtolower(trim($username));

        if (!is_string($username) && !is_numeric($username)) {
            return false;
        }

        $exists = self::where('username', $username)->first();
        if (($user !== null && $exists && $exists->username !== $user->username) || ($exists && $user === null)) {
            return false;
        }

        return preg_match('/^[\pL\pM\pN_-]+$/u', $username) > 0;
    }

    public static function resolveUser()
    {
        return auth('api')->user();
    }

}
