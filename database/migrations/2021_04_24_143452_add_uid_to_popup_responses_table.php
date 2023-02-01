<?php

use Acelle\Model\PopupResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUidToPopupResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('popup_responses', function (Blueprint $table) {
            $table->uuid(PopupResponse::COLUMN_uid);
        });

        // Create UIDs for existing models
        foreach (PopupResponse::all() as $item) {
            // Create new uid
            $uid = uniqid();
            while (PopupResponse::where('uid', '=', $uid)->count() > 0) {
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
        Schema::table('popup_responses', function (Blueprint $table) {
            $table->dropColumn(PopupResponse::COLUMN_uid);
        });
    }
}
