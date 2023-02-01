<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class ResetSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('subscriptions');

        Schema::create('subscriptions', function ($table) {
            $table->increments('id');
            $table->uuid('uid');
            $table->string('user_id');
            $table->string('plan_id');
            $table->string('status');
            $table->timestampTz('trial_ends_at')->nullable();
            $table->timestampTz('ends_at')->nullable();

            $table->timestamps();
        });
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
