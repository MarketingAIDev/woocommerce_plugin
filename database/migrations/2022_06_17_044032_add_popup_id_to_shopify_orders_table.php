<?php

use Acelle\Model\ShopifyOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPopupIdToShopifyOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('shopify_orders', ShopifyOrder::COLUMN_from_emailwish_popup_id))
                $table->unsignedBigInteger(ShopifyOrder::COLUMN_from_emailwish_popup_id);
        });

        $orders = ShopifyOrder::all();
        foreach ($orders as $order) {
            $order->setFromEmailwish();
            $order->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_orders', function (Blueprint $table) {
            if (Schema::hasColumn('shopify_orders', ShopifyOrder::COLUMN_from_emailwish_popup_id))
                $table->dropColumn(ShopifyOrder::COLUMN_from_emailwish_popup_id);
        });
    }
}
