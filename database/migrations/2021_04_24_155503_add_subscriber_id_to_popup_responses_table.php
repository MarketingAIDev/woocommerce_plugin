<?php

use Acelle\Model\PopupResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubscriberIdToPopupResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('popup_responses', function (Blueprint $table) {
            $table->unsignedBigInteger(PopupResponse::COLUMN_subscriber_id)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('popup_responses', function (Blueprint $table) {
            $table->dropColumn(PopupResponse::COLUMN_subscriber_id);
        });
    }
}
