<?php

use Acelle\Model\Segment2ConditionAction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSegment2ConditionActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('segment2_condition_actions')) {
            Schema::drop('segment2_condition_actions');
        }
        Schema::create('segment2_condition_actions', function (Blueprint $table) {
            $table->increments(Segment2ConditionAction::COLUMN_id);
            $table->unsignedBigInteger(Segment2ConditionAction::COLUMN_condition_id);
            $table->unsignedBigInteger(Segment2ConditionAction::COLUMN_action_type_id);
            $table->string(Segment2ConditionAction::COLUMN_count_type);
            $table->unsignedBigInteger(Segment2ConditionAction::COLUMN_count_value);
            $table->string(Segment2ConditionAction::COLUMN_date_type);
            $table->timestamp(Segment2ConditionAction::COLUMN_date_value_date_one);
            $table->timestamp(Segment2ConditionAction::COLUMN_date_value_date_two);
            $table->unsignedBigInteger(Segment2ConditionAction::COLUMN_date_value_int_one);
            $table->unsignedBigInteger(Segment2ConditionAction::COLUMN_date_value_int_two);
            $table->string(Segment2ConditionAction::COLUMN_date_period);
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
        Schema::dropIfExists('segment2_condition_actions');
    }
}
