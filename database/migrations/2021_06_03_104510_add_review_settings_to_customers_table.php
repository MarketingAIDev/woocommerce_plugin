<?php

use Acelle\Model\Customer;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReviewSettingsToCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            if(!Schema::hasColumn('customers', Customer::COLUMN_review_settings)){
                $table->json(Customer::COLUMN_review_settings)->nullable();
            }
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
            if(Schema::hasColumn('customers', Customer::COLUMN_review_settings)){
                $table->dropColumn(Customer::COLUMN_review_settings);
            }
        });
    }
}
