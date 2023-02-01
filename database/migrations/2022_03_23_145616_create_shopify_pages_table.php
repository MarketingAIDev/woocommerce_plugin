<?php

use Acelle\Model\ShopifyPage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopifyPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_pages', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->uuid(ShopifyPage::COLUMN_uid);
            $table->integer(ShopifyPage::COLUMN_customer_id)->unsigned();
            $table->integer(ShopifyPage::COLUMN_shop_id)->unsigned();
            $table->integer(ShopifyPage::COLUMN_shopify_id)->unsigned();
            $table->json(ShopifyPage::COLUMN_data)->nullable();
            $table->string(ShopifyPage::COLUMN_shopify_author, 1000);
            $table->string(ShopifyPage::COLUMN_shopify_title, 1000);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopify_pages');
    }
}
