<?php

use Acelle\Model\ShopifyCustomer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsForPropertyBasedSegmentationToShopifyCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_customers', function (Blueprint $table) {
            if (!Schema::hasColumn('shopify_customers', ShopifyCustomer::COLUMN_accepts_marketing))
                $table->boolean(ShopifyCustomer::COLUMN_accepts_marketing)->default(false);
            if (!Schema::hasColumn('shopify_customers', ShopifyCustomer::COLUMN_accepts_marketing_updated_at))
                $table->dateTime(ShopifyCustomer::COLUMN_accepts_marketing_updated_at)->nullable();
            if (!Schema::hasColumn('shopify_customers', ShopifyCustomer::COLUMN_currency))
                $table->string(ShopifyCustomer::COLUMN_currency, 50)->default("");
            if (!Schema::hasColumn('shopify_customers', ShopifyCustomer::COLUMN_shopify_created_at))
                $table->dateTime(ShopifyCustomer::COLUMN_shopify_created_at)->nullable();
            if (!Schema::hasColumn('shopify_customers', ShopifyCustomer::COLUMN_email))
                $table->string(ShopifyCustomer::COLUMN_email, 255)->default("");
            if (!Schema::hasColumn('shopify_customers', ShopifyCustomer::COLUMN_first_name))
                $table->string(ShopifyCustomer::COLUMN_first_name, 255)->default("");
            if (!Schema::hasColumn('shopify_customers', ShopifyCustomer::COLUMN_last_name))
                $table->string(ShopifyCustomer::COLUMN_last_name, 255)->default("");
            if (!Schema::hasColumn('shopify_customers', ShopifyCustomer::COLUMN_orders_count))
                $table->unsignedBigInteger(ShopifyCustomer::COLUMN_orders_count)->default(0);
            if (!Schema::hasColumn('shopify_customers', ShopifyCustomer::COLUMN_state))
                $table->string(ShopifyCustomer::COLUMN_state, 200)->default("");
            if (!Schema::hasColumn('shopify_customers', ShopifyCustomer::COLUMN_total_spent))
                $table->double(ShopifyCustomer::COLUMN_total_spent)->default(0);
            if (!Schema::hasColumn('shopify_customers', ShopifyCustomer::COLUMN_note))
                $table->string(ShopifyCustomer::COLUMN_note, 2000)->default("");
            if (!Schema::hasColumn('shopify_customers', ShopifyCustomer::COLUMN_phone_number))
                $table->string(ShopifyCustomer::COLUMN_phone_number, 50)->default("");
            if (!Schema::hasColumn('shopify_customers', ShopifyCustomer::COLUMN_verified_email))
                $table->boolean(ShopifyCustomer::COLUMN_verified_email)->default(false);
        });

        foreach (ShopifyCustomer::all() as $item)
            $item->setSegmentationProperties();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_customers', function (Blueprint $table) {
            $table->dropColumn(ShopifyCustomer::COLUMN_accepts_marketing);
            $table->dropColumn(ShopifyCustomer::COLUMN_accepts_marketing_updated_at);
            $table->dropColumn(ShopifyCustomer::COLUMN_currency);
            $table->dropColumn(ShopifyCustomer::COLUMN_shopify_created_at);
            $table->dropColumn(ShopifyCustomer::COLUMN_email);
            $table->dropColumn(ShopifyCustomer::COLUMN_first_name);
            $table->dropColumn(ShopifyCustomer::COLUMN_last_name);
            $table->dropColumn(ShopifyCustomer::COLUMN_orders_count);
            $table->dropColumn(ShopifyCustomer::COLUMN_state);
            $table->dropColumn(ShopifyCustomer::COLUMN_total_spent);
            $table->dropColumn(ShopifyCustomer::COLUMN_note);
            $table->dropColumn(ShopifyCustomer::COLUMN_phone_number);
            $table->dropColumn(ShopifyCustomer::COLUMN_verified_email);
        });
    }
}
