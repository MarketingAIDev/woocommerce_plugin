<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopifyProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_products', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uid');
            $table->integer('customer_id')->unsigned();
            $table->integer('shop_id')->unsigned();

            $table->integer('shopify_id')->unsigned();
            $table->string('shopify_title')->nullable();
            $table->string('shopify_body_html')->nullable();
            $table->string('shopify_vendor')->nullable();
            $table->string('shopify_product_types')->nullable();
            $table->string('shopify_created_at')->nullable();
            $table->string('shopify_handle')->nullable();
            $table->string('shopify_updated_at')->nullable();
            $table->string('shopify_published_at')->nullable();
            $table->string('shopify_template_suffix')->nullable();
            $table->string('shopify_published_scope')->nullable();
            $table->string('shopify_tags')->nullable();
            $table->string('shopify_admin_graphql_api_id')->nullable();
            $table->json('shopify_variants')->nullable();
            $table->json('shopify_options')->nullable();
            $table->json('shopify_images')->nullable();
            $table->json('shopify_image')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('shop_id')->references('id')->on('shopify_shops')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopify_products');
    }
}
