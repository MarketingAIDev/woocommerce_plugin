<?php

use Acelle\Model\PopupImpression;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerIdToPopupImpressionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('popup_impressions', function (Blueprint $table) {
            if (!Schema::hasColumn('popup_impressions', PopupImpression::COLUMN_customer_id))
                $table->unsignedBigInteger(PopupImpression::COLUMN_customer_id);
        });

        foreach (PopupImpression::all() as $item) {
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
        Schema::table('popup_impressions', function (Blueprint $table) {
            if (Schema::hasColumn('popup_impressions', PopupImpression::COLUMN_customer_id))
                $table->dropColumn(PopupImpression::COLUMN_customer_id);
        });
    }
}
