<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSpotify extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table = 'user_spotify';

    protected $fillable = [
        'user_id',
        'spotify_id',
        'spotify_artist_id',
        'display_name',
        'followers',
        'href',
        'access_token',
        'refresh_token',
        'token_expires',
    ];

    protected $dates = [
        'token_expires',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function playlists()
    {
        return $this->hasMany(SpotifyPlaylist::class);
    }
}
