<?php

use Acelle\Model\ShopifyCheckout;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShopifyCustomerIdToShopifyCheckoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_checkouts', function (Blueprint $table) {
            $table->unsignedBigInteger(ShopifyCheckout::COLUMN_shopify_customer_id);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_checkouts', function (Blueprint $table) {
            $table->dropColumn(ShopifyCheckout::COLUMN_shopify_customer_id);
        });
    }
}
