<?php

use Acelle\Model\ShopifyReviewImage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopifyReviewImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_review_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger(ShopifyReviewImage::COLUMN_review_id);
            $table->string(ShopifyReviewImage::COLUMN_image_path, 255);
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
        Schema::dropIfExists('shopify_review_images');
    }
}
