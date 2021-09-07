<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpotifyPlaylist extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table = 'spotify_playlists';

    protected $fillable = [
        'user_spotify_id',
        'playlist_id',
        'name',
        'followers',
        'img_url',
        'public',
        'is_owner',
        'url',
        'uri',
    ];

    public function user_spotify()
    {
        return $this->belongsTo(UserSpotify::class);
    }

    public function tracks()
    {
        return $this->hasMany(SpotifyPlaylistTrack::class);
    }

    public function curator_playlist()
    {
        return $this->hasOne(CuratorPlaylist::class);
    }

}
