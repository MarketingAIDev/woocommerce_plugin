<?php

use Acelle\Model\Customer;
use Illuminate\Database\Migrations\Migration;

class UpdateUserQuota extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $customers = Customer::all();
        foreach ($customers as $customer) {
            $customer->quota = null;
            $customer->save();
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
