<?php

namespace App\Models;

use ArtistRepublik\AROrders\Models\Customer;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Faker\Provider\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Cashier\Billable;

class User extends Model
{
    use SoftDeletes;
    use Billable;
    use CascadeSoftDeletes;

    protected $table = 'users';

    protected $cascadeDeletes = ['curator'];

    protected $fillable = [
        'uuid',
        'api_client_id',
        'external_user_id',
        'first_name',
        'last_name',
        'email',
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

    public static function resolveUser()
    {
        $external_user_id = request()->header('X-EXTERNAL-USER');
        return User::where('external_user_id', $external_user_id)->where('api_client_id', auth()->user()->id)->first();
    }

}
