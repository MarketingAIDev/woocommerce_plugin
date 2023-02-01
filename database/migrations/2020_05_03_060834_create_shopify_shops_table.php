<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopifyShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_shops', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uid');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('customer_id');
            $table->string('name')->nullable();
            $table->string('myshopify_domain')->nullable();
            $table->string('primary_domain')->nullable();
            $table->string('primary_currency')->nullable();
            $table->string('access_token')->nullable();
            $table->string('scope')->nullable();
            $table->string('nonce')->nullable();
            $table->json('theme')->nullable();
            $table->boolean('active')->default(false);
            $table->boolean('initializing')->default(false);
            $table->boolean('initialized')->default(false);
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopify_shops');
    }
}
