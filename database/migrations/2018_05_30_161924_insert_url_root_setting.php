<?php

use Acelle\Model\Setting;
use Illuminate\Database\Migrations\Migration;

class InsertUrlRootSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Trick here
        preg_match('/^https{0,1}:\/\/[^\/]*/', Setting::get('url_delivery_handler'), $result);
        if (!empty($result)) { // for upgrade only, not needed for new installation
            Setting::set('url_root', $result[0]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
