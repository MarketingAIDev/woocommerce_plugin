<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAutomationsShopifyCheckoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automations_shopify_checkouts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('automation2_id');
            $table->unsignedBigInteger('shopify_checkout_id');

            // $table->foreign('automation2_id')->references('id')->on('automation2s')->onDelete('cascade');
            // $table->foreign('shopify_checkout_id')->references('id')->on('shopify_checkouts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('automations_shopify_checkouts');
    }
}
