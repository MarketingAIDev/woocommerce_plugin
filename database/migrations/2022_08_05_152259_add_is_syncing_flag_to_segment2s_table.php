<?php

use Acelle\Model\Segment2;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsSyncingFlagToSegment2sTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('segment2s', function (Blueprint $table) {
            $table->boolean(Segment2::COLUMN_is_syncing)->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('segment2s', function (Blueprint $table) {
            $table->dropColumn(Segment2::COLUMN_is_syncing);
        });
    }
}
