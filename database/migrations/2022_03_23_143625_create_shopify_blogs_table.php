<?php

use Acelle\Model\ShopifyBlog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopifyBlogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_blogs', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->uuid(ShopifyBlog::COLUMN_uid);
            $table->integer(ShopifyBlog::COLUMN_customer_id)->unsigned();
            $table->integer(ShopifyBlog::COLUMN_shop_id)->unsigned();
            $table->integer(ShopifyBlog::COLUMN_shopify_id)->unsigned();
            $table->json(ShopifyBlog::COLUMN_data)->nullable();
            $table->string(ShopifyBlog::COLUMN_shopify_title, 1000);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopify_blogs');
    }
}
