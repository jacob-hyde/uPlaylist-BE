<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpotifyPlaylistTracksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spotify_playlist_tracks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('spotify_playlist_id');
            $table->string('track_id');
            $table->string('name');
            $table->string('artist');
            $table->string('img_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('spotify_playlist_id')->references('id')->on('spotify_playlists');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('spotify_playlist_tracks');
    }
}
