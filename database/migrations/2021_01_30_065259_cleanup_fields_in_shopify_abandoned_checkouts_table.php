<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CleanupFieldsInShopifyAbandonedCheckoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_abandoned_checkouts', function (Blueprint $table) {
            $table->dropColumn('shopify_token');
            $table->dropColumn('shopify_cart_token');
            $table->dropColumn('shopify_email');
            $table->dropColumn('shopify_gateway');
            $table->dropColumn('shopify_buyer_accepts_marketing');
            $table->dropColumn('shopify_created_at');
            $table->dropColumn('shopify_updated_at');
            $table->dropColumn('shopify_completed_at');
            $table->dropColumn('shopify_closed_at');
            $table->dropColumn('shopify_landing_site');
            $table->dropColumn('shopify_note');
            $table->dropColumn('shopify_note_attributes');
            $table->dropColumn('shopify_referring_site');
            $table->dropColumn('shopify_shipping_lines');
            $table->dropColumn('shopify_taxes_included');
            $table->dropColumn('shopify_total_weight');
            $table->dropColumn('shopify_currency');
            $table->dropColumn('shopify_user_id');
            $table->dropColumn('shopify_location_id');
            $table->dropColumn('shopify_source_identifier');
            $table->dropColumn('shopify_source_url');
            $table->dropColumn('shopify_device_id');
            $table->dropColumn('shopify_phone');
            $table->dropColumn('shopify_customer_locale');
            $table->dropColumn('shopify_line_items');
            $table->dropColumn('shopify_name');
            $table->dropColumn('shopify_source');
            $table->dropColumn('shopify_abandoned_checkout_url');
            $table->dropColumn('shopify_discount_codes');
            $table->dropColumn('shopify_tax_lines');
            $table->dropColumn('shopify_source_name');
            $table->dropColumn('shopify_presentment_currency');
            $table->dropColumn('shopify_total_discounts');
            $table->dropColumn('shopify_total_line_items_price');
            $table->dropColumn('shopify_total_price');
            $table->dropColumn('shopify_total_tax');
            $table->dropColumn('shopify_subtotal_price');
            $table->dropColumn('shopify_total_duties');
            $table->dropColumn('shopify_billing_address');
            $table->dropColumn('shopify_shipping_address');
            $table->dropColumn('shopify_customer');
            $table->dropColumn('shopify_customer_id');
            $table->json('data')->nullable();
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
            $table->unsignedInteger('shopify_customer_id');
            $table->dropColumn('data');
        });
    }
}
