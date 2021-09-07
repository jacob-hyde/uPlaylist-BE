<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpotifyPlaylistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spotify_playlists', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_spotify_id');
            $table->string('playlist_id');
            $table->string('name');
            $table->integer('followers')->nullable();
            $table->string('img_url')->nullable();
            $table->boolean('public')->default(0);
            $table->boolean('is_owner')->default(0);
            $table->string('url');
            $table->string('uri');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('user_spotify_id')->references('id')->on('user_spotify');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('spotify_playlists');
    }
}
