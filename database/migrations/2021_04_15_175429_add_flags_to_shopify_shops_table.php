<?php

use Acelle\Model\ShopifyShop;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFlagsToShopifyShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_shops', function (Blueprint $table) {
            $table->dropColumn(ShopifyShop::COLUMN_chat_script_tag_id);
            $table->dropColumn(ShopifyShop::COLUMN_popup_script_tag_id);
            $table->dropColumn(ShopifyShop::COLUMN_review_script_tag_id);
            $table->boolean(ShopifyShop::COLUMN_enable_chat_script)->default(false);
            $table->boolean(ShopifyShop::COLUMN_enable_popup_script)->default(false);
            $table->boolean(ShopifyShop::COLUMN_enable_review_script)->default(false);
            $table->string(ShopifyShop::COLUMN_widget_script_tag_id, 50)->nullable();
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
            $table->dropColumn(ShopifyShop::COLUMN_enable_chat_script);
            $table->dropColumn(ShopifyShop::COLUMN_enable_popup_script);
            $table->dropColumn(ShopifyShop::COLUMN_enable_review_script);
            $table->dropColumn(ShopifyShop::COLUMN_widget_script_tag_id);
            $table->unsignedBigInteger(ShopifyShop::COLUMN_chat_script_tag_id)->nullable();
            $table->unsignedBigInteger(ShopifyShop::COLUMN_popup_script_tag_id)->nullable();
            $table->unsignedBigInteger(ShopifyShop::COLUMN_review_script_tag_id)->nullable();
        });
    }
}
