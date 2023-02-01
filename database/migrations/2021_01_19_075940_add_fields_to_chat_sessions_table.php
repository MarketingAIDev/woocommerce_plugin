<?php

use Acelle\Model\ChatSession;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToChatSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->timestamp(ChatSession::COLUMN_ended_at)->nullable();
            $table->tinyInteger(ChatSession::COLUMN_feedback_rating)->nullable();
            $table->string(ChatSession::COLUMN_feedback_message, 1000)->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->dropColumn(ChatSession::COLUMN_ended_at);
            $table->dropColumn(ChatSession::COLUMN_feedback_rating);
            $table->dropColumn(ChatSession::COLUMN_feedback_message);
        });
    }
}
