<?php

use Acelle\Model\PopupResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePopupResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('popup_responses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger(PopupResponse::COLUMN_popup_id);
            $table->string(PopupResponse::COLUMN_email, 255);
            $table->string(PopupResponse::COLUMN_first_name, 255);
            $table->string(PopupResponse::COLUMN_last_name, 255);
            $table->json(PopupResponse::COLUMN_extra_data);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('popup_responses');
    }
}
