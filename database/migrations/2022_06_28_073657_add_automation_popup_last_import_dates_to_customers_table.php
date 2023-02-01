<?php

use Acelle\Model\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAutomationPopupLastImportDatesToCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            if(!Schema::hasColumn('customers', Customer::COLUMN_automations_imported_at)){
                $table->timestamp(Customer::COLUMN_automations_imported_at)->nullable();
            }
            if(!Schema::hasColumn('customers', Customer::COLUMN_popups_imported_at)){
                $table->timestamp(Customer::COLUMN_popups_imported_at)->nullable();
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
            if(Schema::hasColumn('customers', Customer::COLUMN_automations_imported_at)){
                $table->dropColumn(Customer::COLUMN_automations_imported_at);
            }
            if(Schema::hasColumn('customers', Customer::COLUMN_popups_imported_at)){
                $table->dropColumn(Customer::COLUMN_popups_imported_at);
            }
        });
    }
}
