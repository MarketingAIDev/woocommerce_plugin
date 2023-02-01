<?php

use Acelle\Model\ShopifyPage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeShopifyIdTypeInShopifyPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_pages', function (Blueprint $table) {
            $table->string(ShopifyPage::COLUMN_shopify_id, 20)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_pages', function (Blueprint $table) {
            $table->integer(ShopifyPage::COLUMN_shopify_id)->unsigned()->change();
        });
    }
}
