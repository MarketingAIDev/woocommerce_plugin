<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShopifyDatesToShopifyOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_orders', function (Blueprint $table) {
            $table->dropColumn('total_price');
            $table->decimal('shopify_total_price', 10, 3)->default(0.0);
            $table->timestamp('shopify_created_at')->nullable();
            $table->timestamp('shopify_updated_at')->nullable();
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
            $table->decimal('total_price', 10, 3)->default(0.0);
            $table->dropColumn('shopify_total_price');
            $table->dropColumn('shopify_created_at');
            $table->dropColumn('shopify_updated_at');
        });
    }
}
