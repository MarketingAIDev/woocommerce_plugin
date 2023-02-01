<?php

use Acelle\Model\Segment2ConditionProperty;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToSegment2ConditionPropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('segment2_condition_properties', function (Blueprint $table) {
            if (Schema::hasColumn('segment2_condition_properties', Segment2ConditionProperty::COLUMN_comparison_value___DEPRECATED))
                $table->dropColumn(Segment2ConditionProperty::COLUMN_comparison_value___DEPRECATED);

            if (!Schema::hasColumn('segment2_condition_properties', Segment2ConditionProperty::COLUMN_comparison_value_date))
                $table->timestamp(Segment2ConditionProperty::COLUMN_comparison_value_date);
            if (!Schema::hasColumn('segment2_condition_properties', Segment2ConditionProperty::COLUMN_comparison_value_text))
                $table->string(Segment2ConditionProperty::COLUMN_comparison_value_text);
            if (!Schema::hasColumn('segment2_condition_properties', Segment2ConditionProperty::COLUMN_comparison_value_number))
                $table->double(Segment2ConditionProperty::COLUMN_comparison_value_number);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('segment2_condition_properties', function (Blueprint $table) {
            //
        });
    }
}
