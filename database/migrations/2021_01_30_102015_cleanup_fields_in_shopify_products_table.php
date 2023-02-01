<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CleanupFieldsInShopifyProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_products', function (Blueprint $table) {
            $table->dropColumn('shopify_title');
            $table->dropColumn('shopify_body_html');
            $table->dropColumn('shopify_vendor');
            $table->dropColumn('shopify_product_types');
            $table->dropColumn('shopify_created_at');
            $table->dropColumn('shopify_handle');
            $table->dropColumn('shopify_updated_at');
            $table->dropColumn('shopify_published_at');
            $table->dropColumn('shopify_template_suffix');
            $table->dropColumn('shopify_published_scope');
            $table->dropColumn('shopify_tags');
            $table->dropColumn('shopify_admin_graphql_api_id');
            $table->dropColumn('shopify_variants');
            $table->dropColumn('shopify_options');
            $table->dropColumn('shopify_images');
            $table->dropColumn('shopify_image');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_products', function (Blueprint $table) {
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
        });
    }
}
