<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToShopifyShops extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_shops', function (Blueprint $table) {
            if (!Schema::hasColumn('shopify_shops', 'user_id'))
                $table->unsignedInteger('user_id');
            if (!Schema::hasColumn('shopify_shops', 'new'))
                $table->boolean('new')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_shops', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->dropColumn('new');
        });
    }
}
