<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeys extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('mail_lists', function (Blueprint $table) {
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('mail_lists', function (Blueprint $table) {
            $table->dropForeign('mail_lists_contact_id_foreign');
            $table->dropForeign('mail_lists_customer_id_foreign');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropForeign('contacts_country_id_foreign');
        });
    }
}
