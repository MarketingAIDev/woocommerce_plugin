<?php

use Acelle\Model\Signature;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreColumnsToSignatures extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('signatures', function (Blueprint $table) {
            $table->string(Signature::COLUMN_youtube, 100);
            $table->string(Signature::COLUMN_tiktok, 100);
            $table->string(Signature::COLUMN_pinterest, 100);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('signatures', function (Blueprint $table) {
            $table->dropColumn(Signature::COLUMN_youtube);
            $table->dropColumn(Signature::COLUMN_tiktok);
            $table->dropColumn(Signature::COLUMN_pinterest);
        });
    }
}
