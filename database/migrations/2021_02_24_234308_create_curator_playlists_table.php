<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuratorPlaylistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('curator_playlists', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('curator_id');
            $table->unsignedInteger('spotify_playlist_id');
            $table->string('name');
            $table->string('slug');
            $table->string('username');
            $table->string('url');
            $table->string('img_url')->nullable();
            $table->integer('followers')->default(0);
            $table->integer('amount');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('curator_id')->references('id')->on('curators');
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
        Schema::dropIfExists('curator_playlists');
    }
}
