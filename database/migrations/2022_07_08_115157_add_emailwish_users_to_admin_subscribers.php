<?php

use Acelle\Model\User;
use Illuminate\Database\Migrations\Migration;

class AddEmailwishUsersToAdminSubscribers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (User::all() as $user) {
            $user->createAdminSubscribers();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach (User::all() as $user) {
            $user->removeAdminSubscribers();
        }
    }
}
