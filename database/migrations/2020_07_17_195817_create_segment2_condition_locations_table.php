<?php

use Acelle\Model\Segment2ConditionLocation;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSegment2ConditionLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('segment2_condition_locations', function (Blueprint $table) {
            $table->increments(Segment2ConditionLocation::COLUMN_id);
            $table->unsignedBigInteger(Segment2ConditionLocation::COLUMN_condition_id);
            $table->unsignedBigInteger(Segment2ConditionLocation::COLUMN_location_type_id);
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
        Schema::dropIfExists('segment2_condition_locations');
    }
}
