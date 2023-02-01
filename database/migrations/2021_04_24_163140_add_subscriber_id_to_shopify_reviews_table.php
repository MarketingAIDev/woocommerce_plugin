<?php

use Acelle\Model\ShopifyReview;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubscriberIdToShopifyReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_reviews', function (Blueprint $table) {
            $table->unsignedBigInteger(ShopifyReview::COLUMN_subscriber_id)->nullable();
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
            $table->dropColumn(ShopifyReview::COLUMN_subscriber_id);
        });
    }
}
