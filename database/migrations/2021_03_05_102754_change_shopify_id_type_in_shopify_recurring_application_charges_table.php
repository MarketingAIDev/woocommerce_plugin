<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeShopifyIdTypeInShopifyRecurringApplicationChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_recurring_application_charges', function (Blueprint $table) {
            $table->string('shopify_id', 20)->change();
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
            $table->integer('shopify_id')->unsigned()->change();
        });
    }
}
