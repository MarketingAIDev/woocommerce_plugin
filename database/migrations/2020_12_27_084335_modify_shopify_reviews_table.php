<?php

use Acelle\Model\ShopifyReview;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyShopifyReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_reviews', function (Blueprint $table) {
            $table->unsignedBigInteger(ShopifyReview::COLUMN_shop_id)->change();
            $table->unsignedBigInteger(ShopifyReview::COLUMN_customer_id)->change();
            $table->unsignedSmallInteger(ShopifyReview::COLUMN_stars)->change();
            $table->unsignedBigInteger(ShopifyReview::COLUMN_shopify_product_id);
            $table->dropColumn(ShopifyReview::COLUMN_product_id__DEPRECATED);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_reviews', function (Blueprint $table) {
            $table->integer(ShopifyReview::COLUMN_shop_id)->unsigned()->change();
            $table->integer(ShopifyReview::COLUMN_customer_id)->unsigned()->change();
            $table->integer(ShopifyReview::COLUMN_stars)->change();
            $table->string(ShopifyReview::COLUMN_product_id__DEPRECATED);
            $table->dropColumn(ShopifyReview::COLUMN_shopify_product_id);
        });
    }
}
