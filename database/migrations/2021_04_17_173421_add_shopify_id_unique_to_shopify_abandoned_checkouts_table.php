<?php

use Acelle\Model\ShopifyCheckout;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShopifyIdUniqueToShopifyAbandonedCheckoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_abandoned_checkouts', function (Blueprint $table) {
            $table->unique(ShopifyCheckout::COLUMN_shopify_id);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_abandoned_checkouts', function (Blueprint $table) {
            $table->dropUnique(ShopifyCheckout::COLUMN_shopify_id);
        });
    }
}
