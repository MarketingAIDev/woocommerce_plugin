<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CreateShopifyRecurringApplicationChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_recurring_application_charges', function (Blueprint $table) {
            $table->integer('customer_id')->unsigned();
            $table->increments('id');
            $table->integer('plan_id')->unsigned();
            $table->integer('shop_id')->unsigned();
            $table->string('shopify_api_client_id')->nullable();
            $table->string('shopify_activated_on')->nullable();
            $table->string('shopify_balance_remaining')->nullable();
            $table->string('shopify_balance_used')->nullable();
            $table->string('shopify_billing_on')->nullable();
            $table->string('shopify_cancelled_on')->nullable();
            $table->string('shopify_capped_amount')->nullable();
            $table->string('shopify_confirmation_url')->nullable();
            $table->string('shopify_created_at')->nullable();
            $table->string('shopify_decorated_return_url')->nullable();
            $table->string('shopify_id');
            $table->string('shopify_name')->nullable();
            $table->string('shopify_price')->nullable();
            $table->string('shopify_return_url')->nullable();
            $table->integer('shopify_risk_level')->nullable();
            $table->string('shopify_status')->nullable();
            $table->string('shopify_terms')->nullable();
            $table->string('shopify_test')->nullable();
            $table->integer('shopify_trial_days')->unsigned();
            $table->string('shopify_trial_ends_on')->nullable();
            $table->string('shopify_updated_at')->nullable();
            $table->uuid('uid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopify_recurring_application_charges');
    }
}
