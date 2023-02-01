<?php

use Acelle\Model\ShopifyReview;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopifyReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uid');
            $table->integer(ShopifyReview::COLUMN_shop_id)->unsigned();
            $table->integer(ShopifyReview::COLUMN_customer_id)->unsigned();
            $table->integer(ShopifyReview::COLUMN_stars);
            $table->string(ShopifyReview::COLUMN_product_id__DEPRECATED);
            $table->string(ShopifyReview::COLUMN_reviewer_email);
            $table->string(ShopifyReview::COLUMN_reviewer_name);
            $table->string(ShopifyReview::COLUMN_title);
            $table->string(ShopifyReview::COLUMN_message);
            $table->boolean(ShopifyReview::COLUMN_verified_purchase);
            $table->boolean(ShopifyReview::COLUMN_approved);
            $table->string(ShopifyReview::COLUMN_ip_address);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopify_reviews');
    }
}
