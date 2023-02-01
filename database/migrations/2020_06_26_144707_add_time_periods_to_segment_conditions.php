<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AddTimePeriodsToSegmentConditions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('segment_conditions', function (Blueprint $table) {
            $table->string('special_condition');
            $table->string('time_period');
            $table->integer('time_period_in_last_days');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('segment_conditions', function (Blueprint $table) {
            $table->dropColumn('special_condition');
            $table->dropColumn('time_period');
            $table->dropColumn('time_period_in_last_days');
        });
    }
}
