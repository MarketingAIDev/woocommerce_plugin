<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string topic
 * @property string hmac
 * @property string domain
 * @property string api_version
 * @property string webhook_id
 * @property string content
 */
class ShopifyWebhookLog extends Model
{
    const COLUMN_topic = "topic";
    const COLUMN_hmac = "hmac";
    const COLUMN_domain = "domain";
    const COLUMN_api_version = "api_version";
    const COLUMN_webhook_id = "webhook_id";
    const COLUMN_content = "content";

    static function storeLog($topic, $hmac, $domain, $api_version, $webhook_id, $content): self
    {
        $model = new self();
        $model->topic = $topic;
        $model->hmac = $hmac;
        $model->domain = $domain;
        $model->api_version = $api_version;
        $model->webhook_id = $webhook_id;
        $model->content = $content;

        $model->save();
        return $model;
    }
}
