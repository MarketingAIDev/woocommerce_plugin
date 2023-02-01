<?php

use Acelle\Model\MonthlyCharge;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonthlyChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monthly_charges', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->uuid(MonthlyCharge::COLUMN_uid);
            $table->integer(MonthlyCharge::COLUMN_recurring_charge_id)->unsigned();
            $table->integer(MonthlyCharge::COLUMN_customer_id)->unsigned();
            $table->integer(MonthlyCharge::COLUMN_shop_id)->unsigned();
            $table->integer(MonthlyCharge::COLUMN_plan_id)->unsigned();
            $table->integer(MonthlyCharge::COLUMN_month)->unsigned();
            $table->integer(MonthlyCharge::COLUMN_year)->unsigned();
            $table->double(MonthlyCharge::COLUMN_fixed_price, 16, 2);
            $table->double(MonthlyCharge::COLUMN_usage_charges, 16, 2);
            $table->boolean(MonthlyCharge::COLUMN_usage_charges_billed);
            $table->boolean(MonthlyCharge::COLUMN_affiliate_notified);
            $table->integer(MonthlyCharge::COLUMN_paid_email_count)->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('monthly_charges');
    }
}
