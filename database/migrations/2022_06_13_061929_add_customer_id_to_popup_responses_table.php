<?php

use Acelle\Model\PopupResponse;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerIdToPopupResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('popup_responses', function (Blueprint $table) {
            if (!Schema::hasColumn('popup_responses', PopupResponse::COLUMN_customer_id))
                $table->unsignedBigInteger(PopupResponse::COLUMN_customer_id);
        });

        foreach (PopupResponse::all() as $item) {
            if ($item->popup) {
                $item->customer_id = $item->popup->customer_id;
                $item->save();
            }
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
            if (Schema::hasColumn('popup_impressions', PopupResponse::COLUMN_customer_id))
                $table->dropColumn(PopupResponse::COLUMN_customer_id);
        });
    }
}
