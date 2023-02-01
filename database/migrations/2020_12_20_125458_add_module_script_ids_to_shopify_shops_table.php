<?php

use Acelle\Model\ShopifyShop;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddModuleScriptIdsToShopifyShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_shops', function (Blueprint $table) {
            $table->unsignedBigInteger(ShopifyShop::COLUMN_chat_script_tag_id)->nullable();
            $table->unsignedBigInteger(ShopifyShop::COLUMN_review_script_tag_id)->nullable();
            $table->unsignedBigInteger(ShopifyShop::COLUMN_popup_script_tag_id)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_shops', function (Blueprint $table) {
            $table->dropColumn(ShopifyShop::COLUMN_chat_script_tag_id);
            $table->dropColumn(ShopifyShop::COLUMN_review_script_tag_id);
            $table->dropColumn(ShopifyShop::COLUMN_popup_script_tag_id);
        });
    }
}
