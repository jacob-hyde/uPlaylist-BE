<?php

namespace App\Models;

use ArtistRepublik\AROrders\Models\Cart;
use ArtistRepublik\AROrders\Models\Intent;
use Faker\Provider\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $url
 * @property int $genre_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class UserTrack extends Model
{
    use SoftDeletes;

    public const INTENT_STEP_CREATED = 'user track created';

    /**
     * @var array
     */
    protected $fillable = ['uuid', 'api_client_id', 'external_user_id', 'name', 'url', 'genre_id', 'created_at', 'updated_at', 'deleted_at'];

    public static function boot()
    {
        parent::boot();
        static::creating(function (self $user_track) {
            $user_track->uuid = Uuid::uuid();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function genre()
    {
        return $this->belongsTo(Genre::class);
    }

    public function cart()
    {
        return $this->morphOne(Cart::class, 'cartable');
    }

    public function intent()
    {
        return $this->morphOne(Intent::class, 'steppable');
    }

    public function onPaid()
    {
        //update intent for user track
        if ($this->intent) {
            $this->intent->update([
                'step' => 'paid',
            ]);
        }

        //delete cart
        if ($this->cart) {
            $this->cart->delete();
        }
    }

}
