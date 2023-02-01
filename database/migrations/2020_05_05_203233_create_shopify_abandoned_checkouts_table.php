<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CreateShopifyAbandonedCheckoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_abandoned_checkouts', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uid');
            $table->integer('customer_id')->unsigned();
            $table->integer('shop_id')->unsigned();

            $table->integer('shopify_id')->unsigned();
            $table->string('shopify_token')->nullable();
            $table->string('shopify_cart_token')->nullable();
            $table->string('shopify_email')->nullable();
            $table->string('shopify_gateway')->nullable();
            $table->boolean('shopify_buyer_accepts_marketing');
            $table->string('shopify_created_at')->nullable();
            $table->string('shopify_updated_at')->nullable();
            $table->string('shopify_completed_at')->nullable();
            $table->string('shopify_closed_at')->nullable();
            $table->string('shopify_landing_site')->nullable();
            $table->string('shopify_note')->nullable();
            $table->json('shopify_note_attributes')->nullable();
            $table->string('shopify_referring_site')->nullable();
            $table->json('shopify_shipping_lines')->nullable();
            $table->boolean('shopify_taxes_included')->nullable();
            $table->integer('shopify_total_weight')->nullable();
            $table->string('shopify_currency')->nullable();
            $table->integer('shopify_user_id')->unsigned();
            $table->integer('shopify_location_id')->unsigned();
            $table->string('shopify_source_identifier')->nullable();
            $table->string('shopify_source_url')->nullable();
            $table->string('shopify_device_id')->nullable();
            $table->string('shopify_phone')->nullable();
            $table->string('shopify_customer_locale')->nullable();
            $table->string('shopify_line_items')->nullable();
            $table->string('shopify_name')->nullable();
            $table->string('shopify_source')->nullable();
            $table->string('shopify_abandoned_checkout_url')->nullable();
            $table->json('shopify_discount_codes')->nullable();
            $table->json('shopify_tax_lines')->nullable();
            $table->string('shopify_source_name')->nullable();
            $table->string('shopify_presentment_currency')->nullable();
            $table->string('shopify_total_discounts')->nullable();
            $table->string('shopify_total_line_items_price')->nullable();
            $table->string('shopify_total_price')->nullable();
            $table->string('shopify_total_tax')->nullable();
            $table->string('shopify_subtotal_price')->nullable();
            $table->string('shopify_total_duties')->nullable();
            $table->json('shopify_billing_address')->nullable();
            $table->json('shopify_shipping_address')->nullable();
            $table->json('shopify_customer')->nullable();
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
        Schema::dropIfExists('shopify_abandoned_checkouts');
    }
}
