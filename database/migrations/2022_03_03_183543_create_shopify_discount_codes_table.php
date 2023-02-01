<?php

use Acelle\Model\ShopifyDiscountCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopifyDiscountCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_discount_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->uuid(ShopifyDiscountCode::COLUMN_uid);
            $table->integer(ShopifyDiscountCode::COLUMN_customer_id)->unsigned();
            $table->integer(ShopifyDiscountCode::COLUMN_shop_id)->unsigned();
            $table->integer(ShopifyDiscountCode::COLUMN_shopify_id)->unsigned();
            $table->json(ShopifyDiscountCode::COLUMN_data)->nullable();
            $table->string(ShopifyDiscountCode::COLUMN_discount_code, 1000);
            $table->integer(ShopifyDiscountCode::COLUMN_price_rule_id)->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopify_discount_codes');
    }
}
