<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpotifyPlaylistTrack extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table = 'spotify_playlist_tracks';

    protected $fillable = [
        'spotify_playlist_id',
        'track_id',
        'name',
        'artist',
        'img_url',
    ];

}
