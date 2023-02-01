<?php

use Acelle\Model\ShopifyOrder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShopifyCustomerIdToShopifyOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_orders', function (Blueprint $table) {
            $table->unsignedBigInteger(ShopifyOrder::COLUMN_shopify_customer_id);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_orders', function (Blueprint $table) {
            $table->dropColumn(ShopifyOrder::COLUMN_shopify_customer_id);
        });
    }
}
