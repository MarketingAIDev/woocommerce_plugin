<?php

use Illuminate\Database\Migrations\Migration;

class SetDefaultPaymentMethod extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Acelle\Model\Setting::set('system.payment_gateway', 'direct');
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
