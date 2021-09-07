<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeaturedPlaylistCalendarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('featured_playlist_calendar', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('curator_playlist_id');
            $table->unsignedInteger('order_id')->nullable();
            $table->date('date');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('curator_playlist_id')->references('id')->on('curator_playlists')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('featured_playlist_calendar');
    }
}
