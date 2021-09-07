<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuratorPlaylistGenreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('curator_playlist_genre', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('curator_playlist_id');
            $table->unsignedInteger('genre_id');
            $table->foreign('curator_playlist_id')->references('id')->on('curator_playlists');
            $table->foreign('genre_id')->references('id')->on('genres');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('curator_playlist_genre');
    }
}
