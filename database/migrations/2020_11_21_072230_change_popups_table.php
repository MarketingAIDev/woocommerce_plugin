<?php

use Acelle\Model\Popup;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangePopupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('popups', function (Blueprint $table) {
            $table->string(Popup::COLUMN_title, 255)->change();
            $table->boolean(Popup::COLUMN_active);
            $table->json(Popup::COLUMN_triggers);
            $table->integer(Popup::COLUMN_width);
            $table->integer(Popup::COLUMN_height);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('popups', function (Blueprint $table) {
            $table->dropColumn(Popup::COLUMN_active);
            $table->dropColumn(Popup::COLUMN_triggers);
            $table->dropColumn(Popup::COLUMN_width);
            $table->dropColumn(Popup::COLUMN_height);
        });
    }
}
