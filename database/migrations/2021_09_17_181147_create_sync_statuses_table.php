<?php

use Acelle\Model\SyncStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSyncStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sync_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger(SyncStatus::COLUMN_shopify_shop_id);
            $table->unsignedBigInteger(SyncStatus::COLUMN_customers_synced);
            $table->unsignedBigInteger(SyncStatus::COLUMN_customers_total);
            $table->unsignedBigInteger(SyncStatus::COLUMN_products_synced);
            $table->unsignedBigInteger(SyncStatus::COLUMN_products_total);
            $table->unsignedBigInteger(SyncStatus::COLUMN_orders_synced);
            $table->unsignedBigInteger(SyncStatus::COLUMN_orders_total);
            $table->unsignedBigInteger(SyncStatus::COLUMN_carts_synced);
            $table->unsignedBigInteger(SyncStatus::COLUMN_carts_total);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sync_statuses');
    }
}
