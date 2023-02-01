<?php

use Acelle\Model\ShopifyFulfillment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeShopifyIdTypeInShopifyFulfillmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_fulfillments', function (Blueprint $table) {
            $table->string(ShopifyFulfillment::COLUMN_shopify_id, 20)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_fulfillments', function (Blueprint $table) {
            $table->integer(ShopifyFulfillment::COLUMN_shopify_id)->unsigned()->change();
        });
    }
}
