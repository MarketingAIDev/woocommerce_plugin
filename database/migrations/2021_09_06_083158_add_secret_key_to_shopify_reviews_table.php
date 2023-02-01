<?php

use Acelle\Model\ShopifyReview;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSecretKeyToShopifyReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_reviews', function (Blueprint $table) {
            $table->string(ShopifyReview::COLUMN_secret_key, 255)->default("");
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
            $table->dropColumn(ShopifyReview::COLUMN_secret_key);
        });
    }
}
