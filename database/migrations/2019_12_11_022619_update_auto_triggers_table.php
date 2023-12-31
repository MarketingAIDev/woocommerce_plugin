<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAutoTriggersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::table('auto_triggers', function (Blueprint $table) {
                $table->dropForeign('auto_triggers_auto_event_id_foreign');
                $table->dropColumn('auto_event_id');
            });
        } catch (Exception $ex) {
            //
        }

        // trick: make sure the OLD auto_event_id is no longer there
        Schema::table('auto_triggers', function (Blueprint $table) {
            $table->integer('auto_event_id')->nullable();
        });

        Schema::table('auto_triggers', function (Blueprint $table) {
            $table->dropColumn('auto_event_id');
        });

        Schema::table('auto_triggers', function (Blueprint $table) {
            $table->text('data')->nullable();
            $table->integer('automation2_id')->unsigned();
            $table->dropColumn('preceded_by');
            $table->dropColumn('start_at');

            $table->foreign('automation2_id')->references('id')->on('automation2s')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('auto_triggers', function (Blueprint $table) {
            //$table->dropColumn('data');
            //$table->dropColumn('automation2_id');
        });
    }
}
