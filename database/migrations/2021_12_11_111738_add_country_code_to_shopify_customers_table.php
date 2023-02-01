<?php

use Acelle\Model\ShopifyCustomer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCountryCodeToShopifyCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_customers', function (Blueprint $table) {
            if (!Schema::hasColumn('shopify_customers', ShopifyCustomer::COLUMN_country_code))
                $table->string(ShopifyCustomer::COLUMN_country_code, 10)->default("");
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
            $table->dropColumn(ShopifyCustomer::COLUMN_country_code);
        });
    }
}
