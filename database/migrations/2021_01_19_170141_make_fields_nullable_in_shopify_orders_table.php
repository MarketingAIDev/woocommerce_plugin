<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeFieldsNullableInShopifyOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_orders', function (Blueprint $table) {
            $table->integer('shopify_id')->unsigned()->nullable()->change();
            $table->integer('shopify_number')->unsigned()->nullable()->change();
            $table->integer('shopify_total_weight')->unsigned()->nullable()->change();
            $table->boolean('shopify_taxes_included')->default(false)->change();
            $table->boolean('shopify_confirmed')->default(false)->change();
            $table->boolean('shopify_buyer_accepts_marketing')->default(false)->change();
            $table->integer('shopify_checkout_id')->unsigned()->nullable()->change();
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
            $table->integer('shopify_id')->unsigned()->change();
            $table->integer('shopify_number')->unsigned()->change();
            $table->integer('shopify_total_weight')->unsigned()->change();
            $table->boolean('shopify_taxes_included')->change();
            $table->boolean('shopify_confirmed')->change();
            $table->boolean('shopify_buyer_accepts_marketing')->change();
            $table->integer('shopify_checkout_id')->unsigned()->change();
        });
    }
}
