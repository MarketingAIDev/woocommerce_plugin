<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopifyOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->uuid('uid');
            $table->integer('customer_id')->unsigned();
            $table->integer('shop_id')->unsigned();
            $table->boolean('created_event_triggered')->default(false);

            $table->integer('shopify_id')->unsigned();
            $table->string('shopify_email')->nullable();
            $table->string('shopify_created_at')->nullable();
            $table->string('shopify_updated_at')->nullable();
            $table->string('shopify_completed_at')->nullable();
            $table->string('shopify_closed_at')->nullable();
            $table->string('shopify_processed_at')->nullable();
            $table->string('shopify_cancelled_at')->nullable();
            $table->string('shopify_cancel_reason')->nullable();
            $table->integer('shopify_number')->unsigned();
            $table->string('shopify_note')->nullable();
            $table->string('shopify_token')->nullable();
            $table->string('shopify_gateway')->nullable();
            $table->boolean('shopify_test')->nullable();
            $table->string('shopify_total_price')->nullable();
            $table->string('shopify_subtotal_price')->nullable();
            $table->integer('shopify_total_weight')->unsigned();
            $table->string('shopify_total_tax')->nullable();
            $table->boolean('shopify_taxes_included');
            $table->string('shopify_currency')->nullable();
            $table->string('shopify_financial_status')->nullable();
            $table->boolean('shopify_confirmed');
            $table->string('shopify_total_discounts')->nullable();
            $table->string('shopify_total_line_items_price')->nullable();
            $table->string('shopify_cart_token')->nullable();
            $table->boolean('shopify_buyer_accepts_marketing');
            $table->string('shopify_name')->nullable();
            $table->string('shopify_referring_site')->nullable();
            $table->string('shopify_landing_site')->nullable();
            $table->string('shopify_total_price_usd')->nullable();
            $table->string('shopify_checkout_token')->nullable();
            $table->string('shopify_reference')->nullable();
            $table->string('shopify_user_id')->nullable();
            $table->string('shopify_location_id')->nullable();
            $table->string('shopify_source_identifier')->nullable();
            $table->string('shopify_source_url')->nullable();
            $table->string('shopify_device_id')->nullable();
            $table->string('shopify_phone')->nullable();
            $table->string('shopify_customer_locale')->nullable();
            $table->string('shopify_app_id')->nullable();
            $table->string('shopify_browser_ip')->nullable();
            $table->string('shopify_landing_site_ref')->nullable();
            $table->integer('shopify_order_number')->unsigned();
            $table->json('shopify_discount_applications')->nullable();
            $table->json('shopify_discount_codes')->nullable();
            $table->json('shopify_note_attributes')->nullable();
            $table->json('shopify_payment_gateway_names')->nullable();
            $table->string('shopify_processing_method')->nullable();
            $table->integer('shopify_checkout_id')->unsigned();
            $table->string('shopify_source_name')->nullable();
            $table->string('shopify_fulfillment_status')->nullable();
            $table->json('shopify_tax_lines')->nullable();
            $table->string('shopify_tags')->nullable();
            $table->string('shopify_contact_email')->nullable();
            $table->string('shopify_order_status_url')->nullable();
            $table->string('shopify_presentment_currency')->nullable();
            $table->json('shopify_total_line_items_price_set')->nullable();
            $table->json('shopify_total_discounts_set')->nullable();
            $table->json('shopify_total_shipping_price_set')->nullable();
            $table->json('shopify_subtotal_price_set')->nullable();
            $table->json('shopify_total_price_set')->nullable();
            $table->json('shopify_total_tax_set')->nullable();
            $table->string('shopify_total_tip_received')->nullable();
            $table->json('shopify_original_total_duties_set')->nullable();
            $table->json('shopify_current_total_duties_set')->nullable();
            $table->json('shopify_shipping_lines')->nullable();
            $table->json('shopify_billing_address')->nullable();
            $table->json('shopify_shipping_address')->nullable();
            $table->json('shopify_client_details')->nullable();
            $table->json('shopify_payment_details')->nullable();
            $table->json('shopify_customer')->nullable();
            $table->json('shopify_line_items')->nullable();
            $table->json('shopify_fulfillments')->nullable();
            $table->json('shopify_refunds')->nullable();

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
        Schema::dropIfExists('shopify_orders');
    }
}
