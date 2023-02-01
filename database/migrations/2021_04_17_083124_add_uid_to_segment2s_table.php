<?php

use Acelle\Model\Segment2;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUidToSegment2sTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('segment2s', function (Blueprint $table) {
            $table->uuid(Segment2::COLUMN_uid);
        });

        // Create UIDs for existing models
        foreach (Segment2::all() as $item) {
            // Create new uid
            $uid = uniqid();
            while (Segment2::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;
            $item->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('segment2s', function (Blueprint $table) {
            $table->dropColumn(Segment2::COLUMN_uid);
        });
    }
}
