<?php

use Acelle\Model\ShopifyBlog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeShopifyIdTypeInShopifyBlogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_blogs', function (Blueprint $table) {
            $table->string(ShopifyBlog::COLUMN_shopify_id, 20)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_blogs', function (Blueprint $table) {
            $table->integer(ShopifyBlog::COLUMN_shopify_id)->unsigned()->change();
        });
    }
}
