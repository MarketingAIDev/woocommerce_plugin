<?php

use Acelle\Model\ShopifyOrder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShopifyFulfillmentStatusToShopifyOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_orders', function (Blueprint $table) {
            $table->string(ShopifyOrder::COLUMN_shopify_fulfillment_status, 50);
        });

        // Update existing models
        foreach (ShopifyOrder::all() as $item) {
            $item->shopify_fulfillment_status = $item->getShopifyModel()->fulfillment_status ?? "";
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
        Schema::table('shopify_orders', function (Blueprint $table) {
            $table->dropColumn(ShopifyOrder::COLUMN_shopify_fulfillment_status);
        });
    }
}
