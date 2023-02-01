<?php

use Acelle\Model\ShopifyRecurringApplicationCharge;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRatesToShopifyRecurringApplicationChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_recurring_application_charges', function (Blueprint $table) {
            $table->double(ShopifyRecurringApplicationCharge::COLUMN_price, 16,2);
            $table->double(ShopifyRecurringApplicationCharge::COLUMN_usage_rate, 16,2);
        });

        $charges = ShopifyRecurringApplicationCharge::all();
        foreach ($charges as $charge) {
            $charge->price = $charge->plan->price;
            $charge->usage_rate = $charge->plan->usage_rate;
            $charge->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_recurring_application_charges', function (Blueprint $table) {
            $table->dropColumn(ShopifyRecurringApplicationCharge::COLUMN_price);
            $table->dropColumn(ShopifyRecurringApplicationCharge::COLUMN_usage_rate);
        });
    }
}
