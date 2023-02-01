<?php

use Acelle\Model\Customer;
use Acelle\Model\SalesChannels;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_channels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger(SalesChannels::COLUMN_customer_id);
            $table->unsignedBigInteger(SalesChannels::COLUMN_number_of_sales_from_chats);
            $table->unsignedBigInteger(SalesChannels::COLUMN_number_of_sales_from_emails);
            $table->unsignedBigInteger(SalesChannels::COLUMN_number_of_sales_from_popups);
            $table->decimal(SalesChannels::COLUMN_sales_total_from_chats, 10, 3);
            $table->decimal(SalesChannels::COLUMN_sales_total_from_emails, 10, 3);
            $table->decimal(SalesChannels::COLUMN_sales_total_from_popups, 10, 3);
        });

        foreach (Customer::all() as $customer) {
            SalesChannels::createSalesChannel($customer);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_channels');
    }
}
