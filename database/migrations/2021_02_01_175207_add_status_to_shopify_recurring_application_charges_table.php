<?php

use Acelle\Model\ShopifyRecurringApplicationCharge;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusToShopifyRecurringApplicationChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_recurring_application_charges', function (Blueprint $table) {
            $table->string(ShopifyRecurringApplicationCharge::COLUMN_status, 255);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_recurring_application_charges', function (Blueprint $table) {
            $table->dropColumn(ShopifyRecurringApplicationCharge::COLUMN_status);
        });
    }
}
