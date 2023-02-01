<?php

use Acelle\Model\Segment2ConditionAction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToSegment2ConditionActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('segment2_condition_actions', function (Blueprint $table) {
            if (Schema::hasColumn('segment2_condition_actions', Segment2ConditionAction::COLUMN_date_value_one___DEPRECATED))
                $table->dropColumn(Segment2ConditionAction::COLUMN_date_value_one___DEPRECATED);
            if (Schema::hasColumn('segment2_condition_actions', Segment2ConditionAction::COLUMN_date_value_two___DEPRECATED))
                $table->dropColumn(Segment2ConditionAction::COLUMN_date_value_two___DEPRECATED);
            if (!Schema::hasColumn('segment2_condition_actions', Segment2ConditionAction::COLUMN_date_value_date_one))
                $table->timestamp(Segment2ConditionAction::COLUMN_date_value_date_one);
            if (!Schema::hasColumn('segment2_condition_actions', Segment2ConditionAction::COLUMN_date_value_date_two))
                $table->timestamp(Segment2ConditionAction::COLUMN_date_value_date_two);
            if (!Schema::hasColumn('segment2_condition_actions', Segment2ConditionAction::COLUMN_date_value_int_one))
                $table->unsignedBigInteger(Segment2ConditionAction::COLUMN_date_value_int_one);
            if (!Schema::hasColumn('segment2_condition_actions', Segment2ConditionAction::COLUMN_date_value_int_two))
                $table->unsignedBigInteger(Segment2ConditionAction::COLUMN_date_value_int_two);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('segment2_condition_actions', function (Blueprint $table) {
            //
        });
    }
}
