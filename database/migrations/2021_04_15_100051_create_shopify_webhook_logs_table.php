<?php

use Acelle\Model\ShopifyWebhookLog;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopifyWebhookLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_webhook_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string(ShopifyWebhookLog::COLUMN_topic, 300);
            $table->string(ShopifyWebhookLog::COLUMN_hmac, 300);
            $table->string(ShopifyWebhookLog::COLUMN_domain, 300);
            $table->string(ShopifyWebhookLog::COLUMN_api_version, 300);
            $table->string(ShopifyWebhookLog::COLUMN_webhook_id, 300);
            $table->text(ShopifyWebhookLog::COLUMN_content);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopify_webhook_logs');
    }
}
