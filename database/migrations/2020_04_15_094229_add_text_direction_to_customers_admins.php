<?php

use Acelle\Model\Customer;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTextDirectionToCustomersAdmins extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string(Customer::COLUMN_text_direction)->default('ltr');
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->string('text_direction')->default('ltr');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(Customer::COLUMN_text_direction);
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('text_direction');
        });
    }
}
