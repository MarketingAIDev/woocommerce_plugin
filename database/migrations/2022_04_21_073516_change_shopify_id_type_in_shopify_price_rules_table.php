<?php

use Acelle\Model\ShopifyPriceRule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeShopifyIdTypeInShopifyPriceRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_price_rules', function (Blueprint $table) {
            $table->string(ShopifyPriceRule::COLUMN_shopify_id, 20)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_price_rules', function (Blueprint $table) {
            $table->integer(ShopifyPriceRule::COLUMN_shopify_id)->unsigned()->change();
        });
    }
}
