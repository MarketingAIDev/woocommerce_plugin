<?php

use Acelle\Model\DebugLog;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDebugLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('debug_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string(DebugLog::COLUMN_type, 100);
            $table->string(DebugLog::COLUMN_param_1, 100);
            $table->string(DebugLog::COLUMN_param_2, 100);
            $table->json(DebugLog::COLUMN_data);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('debug_logs');
    }
}
