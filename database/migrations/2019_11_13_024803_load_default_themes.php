<?php

use Acelle\Model\Template;
use Illuminate\Database\Migrations\Migration;

class LoadDefaultThemes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Template::loadFromThemes();
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
