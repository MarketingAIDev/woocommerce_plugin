<?php

use Acelle\Model\ShopifyReview;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AddShopNameToShopifyReviews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('shopify_reviews', ShopifyReview::COLUMN_shop_name))
            $table->string(ShopifyReview::COLUMN_shop_name);
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
            $table->dropColumn(ShopifyReview::COLUMN_shop_name);
        });
    }
}
