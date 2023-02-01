<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSegment2IdToCampaignsListsSegmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaigns_lists_segments', function (Blueprint $table) {
            $table->integer('segment2_id')->unsigned()->nullable();
            $table->foreign('segment2_id')->references('id')->on('segment2s')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaigns_lists_segments', function (Blueprint $table) {
            $table->dropColumn('segment2_id');
        });
    }
}
