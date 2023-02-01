<?php

use Acelle\Model\AbsCampaign;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSalesFieldsToCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->unsignedBigInteger(AbsCampaign::COLUMN_total_number_of_sales);
            $table->decimal(AbsCampaign::COLUMN_sales_total, 10, 3);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn(AbsCampaign::COLUMN_total_number_of_sales);
            $table->dropColumn(AbsCampaign::COLUMN_sales_total);
        });
    }
}
