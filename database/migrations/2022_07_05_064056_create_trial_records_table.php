<?php

use Acelle\Model\ShopifyShop;
use Acelle\Model\TrialRecord;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrialRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trial_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->uuid(TrialRecord::COLUMN_uid);
            $table->string(TrialRecord::COLUMN_myshopify_domain);
            $table->string(TrialRecord::COLUMN_primary_domain);
        });

        foreach (ShopifyShop::all() as $item) {
            TrialRecord::storeRecord($item);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trial_records');
    }
}
