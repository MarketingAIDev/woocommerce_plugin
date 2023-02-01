<?php

use Acelle\Model\ShopifyUsageChargeEntry;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopifyUsageChargeEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_usage_charge_entries', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->uuid('uid');
            $table->integer(ShopifyUsageChargeEntry::COLUMN_customer_id)->unsigned();
            $table->integer(ShopifyUsageChargeEntry::COLUMN_plan_id)->unsigned();
            $table->string(ShopifyUsageChargeEntry::COLUMN_msg_id);
            $table->decimal(ShopifyUsageChargeEntry::COLUMN_usage_charge, 10, 3);
            $table->boolean(ShopifyUsageChargeEntry::COLUMN_charged)->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopify_usage_charge_entries');
    }
}
