<?php

use Acelle\Model\ShopifyOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailwishFlagsToShopifyOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('shopify_orders', ShopifyOrder::COLUMN_from_emailwish_popup))
                $table->boolean(ShopifyOrder::COLUMN_from_emailwish_popup)->default(false);
            if (!Schema::hasColumn('shopify_orders', ShopifyOrder::COLUMN_from_emailwish_chat))
                $table->boolean(ShopifyOrder::COLUMN_from_emailwish_chat)->default(false);
            if (!Schema::hasColumn('shopify_orders', ShopifyOrder::COLUMN_from_emailwish_mail))
                $table->boolean(ShopifyOrder::COLUMN_from_emailwish_mail)->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_orders', function (Blueprint $table) {
            if (Schema::hasColumn('shopify_orders', ShopifyOrder::COLUMN_from_emailwish_popup))
                $table->dropColumn(ShopifyOrder::COLUMN_from_emailwish_popup);
            if (Schema::hasColumn('shopify_orders', ShopifyOrder::COLUMN_from_emailwish_chat))
                $table->dropColumn(ShopifyOrder::COLUMN_from_emailwish_chat);
            if (Schema::hasColumn('shopify_orders', ShopifyOrder::COLUMN_from_emailwish_mail))
                $table->dropColumn(ShopifyOrder::COLUMN_from_emailwish_mail);
        });
    }
}
