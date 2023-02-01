<?php

use Acelle\Model\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments(Customer::COLUMN_id);
            $table->uuid(Customer::COLUMN_uid);
            $table->integer(Customer::COLUMN_user_id)->unsigned();
            $table->integer(Customer::COLUMN_admin_id)->unsigned()->nullable();
            $table->integer(Customer::COLUMN_contact_id)->unsigned()->nullable();
            $table->integer(Customer::COLUMN_language_id)->unsigned()->nullable();
            $table->string(Customer::COLUMN_first_name);
            $table->string(Customer::COLUMN_last_name);
            $table->string(Customer::COLUMN_image)->nullable();
            $table->string(Customer::COLUMN_timezone);
            $table->string(Customer::COLUMN_status)->nullable();
            $table->string(Customer::COLUMN_color_scheme)->nullable();
            $table->binary(Customer::COLUMN_quota)->nullable();
            $table->timestamps();

            // foreign
            $table->foreign(Customer::COLUMN_user_id)->references('id')->on('users')->onDelete('cascade');
            $table->foreign(Customer::COLUMN_admin_id)->references('id')->on('admins');
            $table->foreign(Customer::COLUMN_contact_id)->references('id')->on('contacts');
            $table->foreign(Customer::COLUMN_language_id)->references('id')->on('languages');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('customers');
    }
}
