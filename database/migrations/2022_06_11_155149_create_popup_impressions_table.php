<?php

use Acelle\Model\PopupImpression;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePopupImpressionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('popup_impressions', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->uuid(PopupImpression::COLUMN_uid);
            $table->unsignedBigInteger(PopupImpression::COLUMN_popup_id);
            $table->string(PopupImpression::COLUMN_ip_address, 100);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('popup_impressions');
    }
}
