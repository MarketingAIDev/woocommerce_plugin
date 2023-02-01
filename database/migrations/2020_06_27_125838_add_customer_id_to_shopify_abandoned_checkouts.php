<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AddCustomerIdToShopifyAbandonedCheckouts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_abandoned_checkouts', function (Blueprint $table) {
            $table->unsignedInteger('shopify_customer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_abandoned_checkouts', function (Blueprint $table) {
            $table->dropColumn('shopify_customer_id');
        });
    }
}
