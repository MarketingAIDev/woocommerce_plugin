<?php

use Acelle\Model\ShopifyDiscountCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeShopifyIdTypeInShopifyDiscountCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_discount_codes', function (Blueprint $table) {
            $table->string(ShopifyDiscountCode::COLUMN_shopify_id, 20)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_discount_codes', function (Blueprint $table) {
            $table->integer(ShopifyDiscountCode::COLUMN_shopify_id)->unsigned()->change();
        });
    }
}
