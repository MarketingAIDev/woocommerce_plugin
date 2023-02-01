<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CleanupFieldsInShopifyCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_customers', function (Blueprint $table) {
            $table->dropColumn('shopify_email');
            $table->dropColumn('shopify_accepts_marketing');
            $table->dropColumn('shopify_created_at');
            $table->dropColumn('shopify_updated_at');
            $table->dropColumn('shopify_first_name');
            $table->dropColumn('shopify_last_name');
            $table->dropColumn('shopify_orders_count')->unsigned();
            $table->dropColumn('shopify_state');
            $table->dropColumn('shopify_total_spent');
            $table->dropColumn('shopify_last_order_id')->unsigned();
            $table->dropColumn('shopify_note');
            $table->dropColumn('shopify_verified_email');
            $table->dropColumn('shopify_multipass_identifier');
            $table->dropColumn('shopify_tax_exempt');
            $table->dropColumn('shopify_phone');
            $table->dropColumn('shopify_tags');
            $table->dropColumn('shopify_last_order_name');
            $table->dropColumn('shopify_currency');
            $table->dropColumn('shopify_accepts_marketing_updated_at');
            $table->dropColumn('shopify_admin_graphql_api_id');
            $table->dropColumn('shopify_addresses');
            $table->dropColumn('shopify_tax_exemptions');
            $table->dropColumn('shopify_default_address');
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
        Schema::table('shopify_customers', function (Blueprint $table) {
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
            $table->dropColumn('data');
        });
    }
}
