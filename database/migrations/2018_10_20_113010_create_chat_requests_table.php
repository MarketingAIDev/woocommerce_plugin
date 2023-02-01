<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('guest_id');
            $table->string('operator_id')->nullable();
            $table->integer('guest_is_typing')->default(0);
            $table->integer('operator_is_typing')->default(0);
            $table->string('status', 20);
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
        Schema::dropIfExists('chat_requests');
    }
}
