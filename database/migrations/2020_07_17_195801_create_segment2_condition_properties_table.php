<?php

use Acelle\Model\Segment2ConditionProperty;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSegment2ConditionPropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('segment2_condition_properties', function (Blueprint $table) {
            $table->increments(Segment2ConditionProperty::COLUMN_id);
            $table->unsignedBigInteger(Segment2ConditionProperty::COLUMN_condition_id);
            $table->unsignedBigInteger(Segment2ConditionProperty::COLUMN_property_type_id);
            $table->string(Segment2ConditionProperty::COLUMN_property_data_type);
            $table->string(Segment2ConditionProperty::COLUMN_comparison_type);
            $table->timestamp(Segment2ConditionProperty::COLUMN_comparison_value_date);
            $table->string(Segment2ConditionProperty::COLUMN_comparison_value_text);
            $table->double(Segment2ConditionProperty::COLUMN_comparison_value_number);
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
        Schema::dropIfExists('segment2_condition_properties');
    }
}
