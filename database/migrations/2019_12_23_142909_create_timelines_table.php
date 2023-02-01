<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimelinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('timelines', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uid');
            $table->integer('automation2_id')->unsigned();
            $table->integer('subscriber_id')->unsigned();
            $table->integer('auto_trigger_id')->unsigned();
            $table->string('activity');

            $table->timestamps();

            $table->foreign('automation2_id')->references('id')->on('automation2s')->onDelete('cascade');
            $table->foreign('subscriber_id')->references('id')->on('subscribers')->onDelete('cascade');
            $table->foreign('auto_trigger_id')->references('id')->on('auto_triggers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('timelines', function (Blueprint $table) {
            //
        });
    }
}
