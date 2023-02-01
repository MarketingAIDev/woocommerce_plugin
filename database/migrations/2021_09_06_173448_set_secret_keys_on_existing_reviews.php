<?php

use Acelle\Model\ShopifyReview;
use Illuminate\Database\Migrations\Migration;

class SetSecretKeysOnExistingReviews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (ShopifyReview::all() as $item) {
            if (empty($item->secret_key))
            {
                $item->setSecretKey();
                $item->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Do nothing
    }
}
