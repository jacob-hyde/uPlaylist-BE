<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTracksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_tracks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid');
            $table->unsignedInteger('api_client_id');
            $table->string('external_user_id')->nullable();
            $table->string('name');
            $table->string('url');
            $table->unsignedInteger('genre_id');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('api_client_id')->references('id')->on('api_clients');
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
        Schema::dropIfExists('user_tracks');
    }
}
