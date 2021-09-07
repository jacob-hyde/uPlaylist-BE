<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;
use Spatie\WebhookServer\WebhookCall;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $password
 * @property boolean $active
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class ApiClient extends Authenticatable
{
    use HasApiTokens;

    /**
     * @var array
     */
    protected $fillable = ['uuid','name', 'password', 'active', 'created_at', 'updated_at', 'deleted_at', 'seller_stripe_id', 'fee'];

    public function findForPassport($username)
    {
        return $this->where('uuid', $username)->first();
    }

    public function validateForPassportPasswordGrant($password)
    {
        return Hash::check($password, $this->password);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier() {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims() {
        return [];
    }

    public function webhooks()
    {
        return $this->hasMany(ApiClientWebhook::class);
    }

    public function sendWebhookEvent(string $event, array $payload)
    {
        $payload['event'] = $event;
        foreach ($this->webhooks as $webhook) {
            if (in_array($event, $webhook->events)) {
                WebhookCall::create()
                    ->url($webhook->url)
                    ->useSecret($webhook->secret)
                    ->payload($payload)
                    ->dispatch();
            }
        }
    }

    public static function isAdmin()
    {
        return auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('super-admin'));
    }
}
