<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddVerificationHostnameDkimSelectorToSendingDomains extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sending_domains', function (Blueprint $table) {
            $table->string('verification_hostname')->default('acellemail');
            $table->string('dkim_selector')->default('mailer');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sending_domains', function (Blueprint $table) {
            $table->dropColumn('verification_hostname');
            $table->dropColumn('dkim_selector');
        });
    }
}
