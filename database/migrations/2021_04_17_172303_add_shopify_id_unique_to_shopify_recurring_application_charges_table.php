<?php

use Acelle\Model\ShopifyRecurringApplicationCharge;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShopifyIdUniqueToShopifyRecurringApplicationChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_recurring_application_charges', function (Blueprint $table) {
            $table->unique(ShopifyRecurringApplicationCharge::COLUMN_shopify_id);
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
            $table->dropUnique(ShopifyRecurringApplicationCharge::COLUMN_shopify_id);
        });
    }
}
