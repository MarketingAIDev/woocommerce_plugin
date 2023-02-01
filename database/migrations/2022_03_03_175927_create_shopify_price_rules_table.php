<?php

use Acelle\Model\ShopifyPriceRule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopifyPriceRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_price_rules', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->uuid(ShopifyPriceRule::COLUMN_uid);
            $table->integer(ShopifyPriceRule::COLUMN_customer_id)->unsigned();
            $table->integer(ShopifyPriceRule::COLUMN_shop_id)->unsigned();
            $table->integer(ShopifyPriceRule::COLUMN_shopify_id)->unsigned();
            $table->json(ShopifyPriceRule::COLUMN_data)->nullable();
            $table->string(ShopifyPriceRule::COLUMN_shopify_title, 1000);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopify_price_rules');
    }
}
