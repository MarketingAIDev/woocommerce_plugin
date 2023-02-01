<?php

use Acelle\Model\ChatSession;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastMessageAtInChatSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->timestamp(ChatSession::COLUMN_last_message_at)->nullable();
        });

        foreach (ChatSession::all() as $item) {
            $last_message = $item->getLastMessageAttribute();
            if ($last_message) {
                $item->last_message_at = $last_message->created_at;
                $item->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->dropColumn(ChatSession::COLUMN_last_message_at);
        });
    }
}
