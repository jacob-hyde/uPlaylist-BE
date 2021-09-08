<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Laravel\Scout\Searchable;

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
    use Searchable;

    /**
     * @var array
     */
    protected $fillable = ['id', 'curator_id', 'spotify_playlist_id', 'name', 'slug', 'username', 'url', 'img_url', 'followers', 'amount', 'created_at', 'updated_at', 'deleted_at'];

    public function getRouteKeyName()
    {
        return 'slug';
    }

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

    public function spotify_playlist()
    {
        return $this->belongsTo(SpotifyPlaylist::class);
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

    public function updateFromSpotify()
    {
        $spotify_playlist = SpotifyPlaylist::findOrFail($this->spotify_playlist_id);
        $this->name = $spotify_playlist->name;
        $this->username = $spotify_playlist->user_spotify->display_name;
        $this->followers = $spotify_playlist->followers;
        $this->url = $spotify_playlist->url;
        $this->img_url = $spotify_playlist->img_url;

        $slug = str_slug($this->name);
        if ($this->id) {
            $slug_count = self::where('slug', $slug)->where('id', '!=', $this->id)->count();
        } else {
            $slug_count = self::where('slug', $slug)->count();
        }

        if ($slug_count > 0) {
            $slug = $slug . '-' . ($slug_count + 1);
        }

        $this->slug = $slug;
    }

    public function searchableAs()
    {
        return 'playlists_index';
    }


}
