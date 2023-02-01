<?php

use Acelle\Model\ShopifyRecurringApplicationCharge;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTrialEndsAtToShopifyRecurringApplicationChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_recurring_application_charges', function (Blueprint $table) {
            $table->timestamp(ShopifyRecurringApplicationCharge::COLUMN_trial_ends_at)->nullable();
        });

        $charges = ShopifyRecurringApplicationCharge::all();
        foreach ($charges as $charge) {
            $charge->trial_ends_at = Carbon::parse($charge->getShopifyModel()->trial_ends_on ?? null)->setTimezone('UTC');
            $charge->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_recurring_application_charges', function (Blueprint $table) {
            $table->dropColumn(ShopifyRecurringApplicationCharge::COLUMN_trial_ends_at);
        });
    }
}
