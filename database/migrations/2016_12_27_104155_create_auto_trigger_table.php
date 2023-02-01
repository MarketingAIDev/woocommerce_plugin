<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutoTriggerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auto_triggers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('subscriber_id')->unsigned()->nullable();
            $table->json('extra_data')->nullable();
            $table->integer('preceded_by')->unsigned()->nullable();
            $table->timestamp('start_at')->nullable();

            $table->timestamps();

            $table->foreign('preceded_by')->references('id')->on('auto_triggers')->onDelete('cascade');
            $table->foreign('subscriber_id')->references('id')->on('subscribers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('auto_triggers');
    }
}
