<?php

use Acelle\Model\ShopifyFulfillment;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopifyFulfillmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_fulfillments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->uuid(ShopifyFulfillment::COLUMN_uid);
            $table->unsignedBigInteger(ShopifyFulfillment::COLUMN_customer_id);
            $table->unsignedBigInteger(ShopifyFulfillment::COLUMN_shop_id);
            $table->unsignedBigInteger(ShopifyFulfillment::COLUMN_shopify_order_id);
            $table->unsignedBigInteger(ShopifyFulfillment::COLUMN_shopify_customer_id);
            $table->unsignedBigInteger(ShopifyFulfillment::COLUMN_shopify_id);
            $table->text(ShopifyFulfillment::COLUMN_data);
            $table->string(ShopifyFulfillment::COLUMN_shopify_shipment_status, 50);
            $table->timestamp(ShopifyFulfillment::COLUMN_shopify_created_at)->nullable();
            $table->timestamp(ShopifyFulfillment::COLUMN_shopify_updated_at)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopify_fulfillments');
    }
}
