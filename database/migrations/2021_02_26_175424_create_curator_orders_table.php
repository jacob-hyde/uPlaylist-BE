<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuratorOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('curator_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid');
            $table->unsignedInteger('api_client_id')->nullable();
            $table->unsignedInteger('user_id');
            $table->string('external_user_id')->nullable();
            $table->unsignedInteger('order_id')->nullable();
            $table->unsignedInteger('curator_id');
            $table->unsignedInteger('curator_playlist_id');
            $table->integer('amount');
            $table->integer('playlist_price');
            $table->unsignedInteger('user_track_id');
            $table->enum('status', ['pending', 'approved', 'declined'])->default('pending');
            $table->dateTime('status_changed_at')->nullable();
            $table->text('feedback')->nullable();
            $table->boolean('added_to_playlist')->default(0);
            $table->boolean('is_refunded')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('api_client_id')->references('id')->on('api_clients');
            $table->foreign('order_id')->references('id')->on('orders');
            $table->foreign('curator_id')->references('id')->on('curators');
            $table->foreign('curator_playlist_id')->references('id')->on('curator_playlists');
            $table->foreign('user_track_id')->references('id')->on('user_tracks');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('curator_orders');
    }
}
