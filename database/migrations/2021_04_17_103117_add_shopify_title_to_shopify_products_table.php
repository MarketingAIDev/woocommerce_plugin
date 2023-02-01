<?php

use Acelle\Model\ShopifyOrder;
use Acelle\Model\ShopifyProduct;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShopifyTitleToShopifyProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_products', function (Blueprint $table) {
            if(!Schema::hasColumn('shopify_products', ShopifyProduct::COLUMN_shopify_title))
                $table->string(ShopifyProduct::COLUMN_shopify_title, 1000);
        });

        // Fill title for existing models
        foreach (ShopifyProduct::all() as $item) {
            $item->shopify_title = $item->getShopifyModel()->title ?? "Untitled";
            $item->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_products', function (Blueprint $table) {
            if(Schema::hasColumn('shopify_products', ShopifyProduct::COLUMN_shopify_title))
                $table->dropColumn(ShopifyProduct::COLUMN_shopify_title);
        });
    }
}
