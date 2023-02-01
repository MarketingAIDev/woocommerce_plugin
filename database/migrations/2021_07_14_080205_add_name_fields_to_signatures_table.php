<?php

use Acelle\Model\Signature;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNameFieldsToSignaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('signatures', function (Blueprint $table) {
            $table->dropColumn(Signature::COLUMN_content__DEPRECATED);
            $table->string(Signature::COLUMN_full_name, 100);
            $table->string(Signature::COLUMN_designation, 100);
            $table->string(Signature::COLUMN_website, 100);
            $table->string(Signature::COLUMN_phone, 100);
            $table->string(Signature::COLUMN_skype, 100);
            $table->string(Signature::COLUMN_logo_path, 100);
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
            $table->longtext(Signature::COLUMN_content__DEPRECATED);
            $table->dropColumn(Signature::COLUMN_full_name);
            $table->dropColumn(Signature::COLUMN_designation);
            $table->dropColumn(Signature::COLUMN_website);
            $table->dropColumn(Signature::COLUMN_phone);
            $table->dropColumn(Signature::COLUMN_skype);
            $table->dropColumn(Signature::COLUMN_logo_path);
        });
    }
}
