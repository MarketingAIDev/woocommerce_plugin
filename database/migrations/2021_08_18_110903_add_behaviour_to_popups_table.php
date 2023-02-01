<?php

use Acelle\Model\Popup;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBehaviourToPopupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('popups', function (Blueprint $table) {
            $table->string(Popup::COLUMN_behaviour, 100)->default('till_submission');
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
            $table->dropColumn(Popup::COLUMN_behaviour, 100);
        });
    }
}
