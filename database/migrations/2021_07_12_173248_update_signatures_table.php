<?php

use Acelle\Model\Signature;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateSignaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('signatures', function (Blueprint $table) {
            $table->string(Signature::COLUMN_facebook, 100);
            $table->string(Signature::COLUMN_instagram, 100);
            $table->string(Signature::COLUMN_linkedin, 100);
            $table->string(Signature::COLUMN_twitter, 100);
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
            $table->dropColumn(Signature::COLUMN_facebook);
            $table->dropColumn(Signature::COLUMN_instagram);
            $table->dropColumn(Signature::COLUMN_linkedin);
            $table->dropColumn(Signature::COLUMN_twitter);
        });
    }
}
