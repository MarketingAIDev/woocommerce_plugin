<?php

use Acelle\Model\Layout;
use Illuminate\Database\Migrations\Migration;

class CreateWelcomeLayout extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /** @var Layout $old */
        $old = Layout::where('alias', 'registration_confirmation_email')->first();
        $new = new Layout();

        $new->subject = "Welcome to Emailwish!";
        $new->type = 'email';
        $new->group_name = $old->group_name ?? "";
        $new->content = $old->content ?? "";
        $new->alias = 'welcome_email';
        $new->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $new = Layout::where('alias', 'welcome_email')->first();
        $new->delete();
    }
}
