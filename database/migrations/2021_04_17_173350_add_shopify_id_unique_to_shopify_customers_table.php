<?php

use Acelle\Model\ShopifyCustomer;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShopifyIdUniqueToShopifyCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_customers', function (Blueprint $table) {
            $table->unique(ShopifyCustomer::COLUMN_shopify_id);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_customers', function (Blueprint $table) {
            $table->dropUnique(ShopifyCustomer::COLUMN_shopify_id);
        });
    }
}
