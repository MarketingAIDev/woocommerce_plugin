<?php

use Acelle\Model\ShopifyCheckout;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDateFieldsToShopifyCheckoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_checkouts', function (Blueprint $table) {
            $table->timestamp(ShopifyCheckout::COLUMN_shopify_created_at)->nullable();
            $table->timestamp(ShopifyCheckout::COLUMN_shopify_updated_at)->nullable();
            $table->timestamp(ShopifyCheckout::COLUMN_shopify_completed_at)->nullable();
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
            $table->dropColumn(ShopifyCheckout::COLUMN_shopify_created_at);
            $table->dropColumn(ShopifyCheckout::COLUMN_shopify_updated_at);
            $table->dropColumn(ShopifyCheckout::COLUMN_shopify_completed_at);
        });
    }
}
