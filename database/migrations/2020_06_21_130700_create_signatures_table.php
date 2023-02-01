<?php

use Acelle\Model\Signature;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSignaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('signatures', function (Blueprint $table) {
            $table->increments(Signature::COLUMN_id);
            $table->uuid(Signature::COLUMN_uid);
            $table->integer(Signature::COLUMN_customer_id)->unsigned();
            $table->longtext(Signature::COLUMN_content__DEPRECATED);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('signatures');
    }
}
