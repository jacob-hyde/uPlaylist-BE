<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuratorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('curators', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('api_client_id')->nullable();
            $table->unsignedInteger('user_id');
            $table->integer('price')->default(1);
            $table->integer('paid_out_amount')->nullable();
            $table->dateTime('last_payout')->nullable();
            $table->boolean('suspended')->default(0);
            $table->dateTime('suspended_at')->nullable();
            $table->integer('no_feedback_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('api_client_id')->references('id')->on('api_clients');
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
        Schema::dropIfExists('curators');
    }
}
