<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreatePlansEmailVerificationServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans_email_verification_servers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('server_id')->unsigned();
            $table->integer('plan_id')->unsigned();

            $table->timestamps();

            $table->foreign('server_id')->references('id')->on('email_verification_servers')->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('plans_email_verification_servers');
    }
}
