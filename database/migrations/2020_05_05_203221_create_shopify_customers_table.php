<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopifyCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_customers', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uid');
            $table->integer('customer_id')->unsigned();
            $table->integer('shop_id')->unsigned();

            $table->integer('shopify_id')->unsigned();
            $table->string('shopify_email')->nullable();
            $table->boolean('shopify_accepts_marketing');
            $table->string('shopify_created_at')->nullable();
            $table->string('shopify_updated_at')->nullable();
            $table->string('shopify_first_name')->nullable();
            $table->string('shopify_last_name')->nullable();
            $table->integer('shopify_orders_count')->unsigned();
            $table->string('shopify_state')->nullable();
            $table->string('shopify_total_spent')->nullable();
            $table->integer('shopify_last_order_id')->unsigned()->nullable();
            $table->string('shopify_note')->nullable();
            $table->boolean('shopify_verified_email');
            $table->string('shopify_multipass_identifier')->nullable();
            $table->boolean('shopify_tax_exempt');
            $table->string('shopify_phone')->nullable();
            $table->string('shopify_tags')->nullable();
            $table->string('shopify_last_order_name')->nullable();
            $table->string('shopify_currency')->nullable();
            $table->string('shopify_accepts_marketing_updated_at')->nullable();
            $table->string('shopify_admin_graphql_api_id')->nullable();
            $table->json('shopify_addresses')->nullable();
            $table->json('shopify_tax_exemptions')->nullable();
            $table->json('shopify_default_address')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('shop_id')->references('id')->on('shopify_shops')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopify_customers');
    }
}
