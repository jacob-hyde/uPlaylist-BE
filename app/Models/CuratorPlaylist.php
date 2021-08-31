<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property int $curator_id
 * @property string $name
 * @property string $username
 * @property int $followers
 * @property int $amount
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Curator $curator
 */
class CuratorPlaylist extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = ['id', 'curator_id', 'spotify_playlist_id', 'name', 'username', 'url', 'img_url', 'followers', 'amount', 'created_at', 'updated_at', 'deleted_at'];

    public function scopeCuratorNotSuspended($query)
    {
        $query->whereHas('curator', function ($query) {
            $query->where('suspended', '=', 0)->whereNull('deleted_at');
        });
    }

    public function scopeCuratorVerified($query)
    {
        $query->whereHas('curator', function ($query) {
            return $query->where('verified', 1);
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function curator()
    {
        return $this->belongsTo(Curator::class)->withTrashed();
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }

    public function curatorOrders()
    {
        return $this->hasMany(CuratorOrder::class);
    }

    public function getPlacementAttribute()
    {
        $reviewedOrdersCount = $this->curatorOrders()->reviewed()->count();
        if ($reviewedOrdersCount > 0) {
            return Cache::remember('playlist_placement:'.$this->id, 14400, function () use ($reviewedOrdersCount) {
                return 100 * round($this->curatorOrders()->approved()->count() / $reviewedOrdersCount, 3);
            });
        }

        return 100;
    }
}
