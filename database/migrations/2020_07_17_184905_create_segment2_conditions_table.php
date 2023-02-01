<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSegment2ConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('segment2_conditions')) {
            Schema::drop('segment2_conditions');
        }
        Schema::create('segment2_conditions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('group_id');
            $table->string('condition_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('segment2_conditions');
    }
}
