<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiClientWebhooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_client_webhooks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid');
            $table->unsignedInteger('api_client_id');
            $table->string('url');
            $table->json('events');
            $table->string('secret');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('api_client_id')->references('id')->on('api_clients');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_client_webhooks');
    }
}
