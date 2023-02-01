<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CleanupFieldsInShopifyRecurringApplicationChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_recurring_application_charges', function (Blueprint $table) {
            $table->dropColumn('shopify_api_client_id');
            $table->dropColumn('shopify_activated_on');
            $table->dropColumn('shopify_balance_remaining');
            $table->dropColumn('shopify_balance_used');
            $table->dropColumn('shopify_billing_on');
            $table->dropColumn('shopify_cancelled_on');
            $table->dropColumn('shopify_capped_amount');
            $table->dropColumn('shopify_confirmation_url');
            $table->dropColumn('shopify_created_at');
            $table->dropColumn('shopify_decorated_return_url');
            $table->dropColumn('shopify_name');
            $table->dropColumn('shopify_price');
            $table->dropColumn('shopify_return_url');
            $table->dropColumn('shopify_risk_level');
            $table->dropColumn('shopify_status');
            $table->dropColumn('shopify_terms');
            $table->dropColumn('shopify_test');
            $table->dropColumn('shopify_trial_days');
            $table->dropColumn('shopify_trial_ends_on');
            $table->dropColumn('shopify_updated_at');
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
        Schema::table('shopify_recurring_application_charges', function (Blueprint $table) {
            $table->dropColumn('shopify_api_client_id')->nullable();
            $table->dropColumn('shopify_activated_on')->nullable();
            $table->dropColumn('shopify_balance_remaining')->nullable();
            $table->dropColumn('shopify_balance_used')->nullable();
            $table->dropColumn('shopify_billing_on')->nullable();
            $table->dropColumn('shopify_cancelled_on')->nullable();
            $table->dropColumn('shopify_capped_amount')->nullable();
            $table->dropColumn('shopify_confirmation_url')->nullable();
            $table->dropColumn('shopify_created_at')->nullable();
            $table->dropColumn('shopify_decorated_return_url')->nullable();
            $table->dropColumn('shopify_name')->nullable();
            $table->dropColumn('shopify_price')->nullable();
            $table->dropColumn('shopify_return_url')->nullable();
            $table->integer('shopify_risk_level')->nullable();
            $table->dropColumn('shopify_status')->nullable();
            $table->dropColumn('shopify_terms')->nullable();
            $table->dropColumn('shopify_test')->nullable();
            $table->integer('shopify_trial_days')->unsigned();
            $table->dropColumn('shopify_trial_ends_on')->nullable();
            $table->dropColumn('shopify_updated_at')->nullable();
            $table->dropColumn('data');
        });
    }
}
