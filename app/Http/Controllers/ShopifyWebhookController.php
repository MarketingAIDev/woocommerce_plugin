<?php

namespace Acelle\Http\Controllers;

use Acelle\Helpers\ShopifyHelper;
use Acelle\Jobs\RunAutomationWithContext;
use Acelle\Library\Automation\ShopifyAutomationContext;
use Acelle\Model\Automation2;
use Acelle\Model\ShopifyCheckout;
use Acelle\Model\ShopifyCustomer;
use Acelle\Model\ShopifyFulfillment;
use Acelle\Model\ShopifyOrder;
use Acelle\Model\ShopifyProduct;
use Acelle\Model\ShopifyShop;
use Acelle\Model\ShopifyWebhookLog;
use Acelle\Notifications\GDPRDataRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ShopifyWebhookController extends Controller
{
    const TOPIC_carts_create = 'carts/create';
    const TOPIC_carts_update = 'carts/update';
    const TOPIC_checkouts_create = 'checkouts/create';
    const TOPIC_checkouts_update = 'checkouts/update';
    const TOPIC_checkouts_delete = 'checkouts/delete'; // doesn't exist
    const TOPIC_collections_create = 'collections/create';
    const TOPIC_collections_update = 'collections/update';
    const TOPIC_collections_delete = 'collections/delete';
    const TOPIC_collection_listings_add = 'collection_listings/add';
    const TOPIC_collection_listings_update = 'collection_listings/update';
    const TOPIC_collection_listings_remove = 'collection_listings/remove';
    const TOPIC_customers_create = 'customers/create';
    const TOPIC_customers_disable = 'customers/disable';
    const TOPIC_customers_enable = 'customers/enable';
    const TOPIC_customers_update = 'customers/update';
    const TOPIC_customers_delete = 'customers/delete';
    const TOPIC_customer_groups_create = 'customer_groups/create';
    const TOPIC_customer_groups_update = 'customer_groups/update';
    const TOPIC_customer_groups_delete = 'customer_groups/delete';
    const TOPIC_disputes_create = 'disputes/create';
    const TOPIC_disputes_update = 'disputes/update';
    const TOPIC_domains_create = 'domains/create';
    const TOPIC_domains_update = 'domains/update';
    const TOPIC_domains_delete = 'domains/delete';
    const TOPIC_draft_orders_create = 'draft_orders/create';
    const TOPIC_draft_orders_update = 'draft_orders/update';
    const TOPIC_draft_orders_delete = 'draft_orders/delete';
    const TOPIC_fulfillments_create = 'fulfillments/create';
    const TOPIC_fulfillments_update = 'fulfillments/update';
    const TOPIC_fulfillments_delete = 'fulfillments/delete';
    const TOPIC_fulfillment_events_create = 'fulfillment_events/create';
    const TOPIC_fulfillment_events_delete = 'fulfillment_events/delete';
    const TOPIC_inventory_items_create = 'inventory_items/create';
    const TOPIC_inventory_items_update = 'inventory_items/update';
    const TOPIC_inventory_items_delete = 'inventory_items/delete';
    const TOPIC_inventory_levels_connect = 'inventory_levels/connect';
    const TOPIC_inventory_levels_disconnect = 'inventory_levels/disconnect';
    const TOPIC_inventory_levels_delete = 'inventory_levels/delete';
    const TOPIC_locations_create = 'locations/create';
    const TOPIC_locations_update = 'locations/update';
    const TOPIC_locations_delete = 'locations/delete';
    const TOPIC_orders_cancelled = 'orders/cancelled';
    const TOPIC_orders_create = 'orders/create';
    const TOPIC_orders_fulfilled = 'orders/fulfilled';
    const TOPIC_orders_paid = 'orders/paid';
    const TOPIC_orders_partially_fulfilled = 'orders/partially_fulfilled';
    const TOPIC_orders_updated = 'orders/updated';
    const TOPIC_orders_delete = 'orders/delete';
    const TOPIC_orders_edited = 'orders/edited';
    const TOPIC_order_transactions_create = 'order_transactions/create';
    const TOPIC_products_create = 'products/create';
    const TOPIC_products_update = 'products/update';
    const TOPIC_products_delete = 'products/delete';
    const TOPIC_product_listings_add = 'product_listings/add';
    const TOPIC_product_listings_update = 'product_listings/update';
    const TOPIC_product_listings_remove = 'product_listings/remove';
    const TOPIC_profiles_create = 'profiles/create';
    const TOPIC_profiles_update = 'profiles/update';
    const TOPIC_profiles_delete = 'profiles/delete';
    const TOPIC_refunds_create = 'refunds/create';
    const TOPIC_app_uninstalled = 'app/uninstalled';
    const TOPIC_shop_update = 'shop/update';
    const TOPIC_locales_create = 'locales/create';
    const TOPIC_locales_update = 'locales/update';
    const TOPIC_tender_transactions_create = 'tender_transactions/create';
    const TOPIC_themes_create = 'themes/create';
    const TOPIC_themes_publish = 'themes/publish';
    const TOPIC_themes_update = 'themes/update';
    const TOPIC_themes_delete = 'themes/delete';

    const TOPIC_gdpr_customers_redact = 'customers/redact';
    const TOPIC_gdpr_shop_redact = 'shop/redact';
    const TOPIC_gdpr_customers_data_request = 'customers/data_request';

    const TOPICS_REQUIRED = [
        self::TOPIC_checkouts_create,
        self::TOPIC_checkouts_update,
        self::TOPIC_customers_create,
        self::TOPIC_customers_disable,
        self::TOPIC_customers_enable,
        self::TOPIC_customers_update,
        self::TOPIC_customers_delete,
        self::TOPIC_app_uninstalled,
        self::TOPIC_products_create,
        self::TOPIC_products_update,
        self::TOPIC_products_delete,
        self::TOPIC_orders_cancelled,
        self::TOPIC_orders_create,
        self::TOPIC_orders_edited,
        self::TOPIC_orders_fulfilled,
        self::TOPIC_orders_paid,
        self::TOPIC_orders_partially_fulfilled,
        self::TOPIC_orders_delete,
        self::TOPIC_fulfillments_create,
        self::TOPIC_fulfillments_update,
    ];

    function process(Request $request)
    {
        $header_topic = $request->header('X-Shopify-Topic') ?? "";
        $header_hmac = $request->header('X-Shopify-Hmac-Sha256') ?? "";
        $header_shop_domain = $request->header('X-Shopify-Shop-Domain') ?? "";
        $header_api_version = $request->header('X-Shopify-API-Version') ?? "";
        $header_webhook_id = $request->header('X-Shopify-Webhook-Id') ?? "";

        $content = $request->getContent() ?? "[]";
        $data = json_decode($content, true);

        ShopifyWebhookLog::storeLog($header_topic, $header_hmac, $header_shop_domain, $header_api_version, $header_webhook_id, $content);

        if (empty($data))
            return response("No content received", 422);

        // Verify the webhook
        $h = new ShopifyHelper(null);
        if (!$h->verify_webhook($content, $header_hmac))
            return response('Invalid webhook request', 401);

        $shop = ShopifyShop::findByMyShopifyDomain($header_shop_domain);
        if (!$shop)
            return response('Shop not found', 201);

        switch ($header_topic) {
            // GDPR Requests
            case self::TOPIC_gdpr_customers_redact:
                return $this->gdpr_customers_redact($shop, $data);
            case self::TOPIC_gdpr_shop_redact:
                return $this->gdpr_shop_redact($shop, $data);
            case self::TOPIC_gdpr_customers_data_request:
                return $this->gdpr_customer_data_request($shop, $data);

            // Customers
            case self::TOPIC_customers_create:
            case self::TOPIC_customers_disable:
            case self::TOPIC_customers_enable:
            case self::TOPIC_customers_update:
                return $this->storeCustomer($shop, $data);
            case self::TOPIC_customers_delete:
                return $this->deleteCustomer($shop, $data);

            // Products
            case self::TOPIC_products_create:
            case self::TOPIC_products_update:
                return $this->storeProduct($shop, $data);
            case self::TOPIC_products_delete:
                return $this->deleteProduct($shop, $data);

            case self::TOPIC_checkouts_create:
            case self::TOPIC_checkouts_update:
                return $this->storeCheckout($shop, $data);
            //case self::TOPIC_checkouts_delete:
            //    return $this->deleteCheckout($shop, $data);

            // Orders
            case self::TOPIC_orders_cancelled:
            case self::TOPIC_orders_create:
            case self::TOPIC_orders_edited:
            case self::TOPIC_orders_fulfilled:
            case self::TOPIC_orders_paid:
            case self::TOPIC_orders_partially_fulfilled:
                return $this->storeOrder($shop, $data, $header_topic);
            case self::TOPIC_orders_delete:
                return $this->deleteOrder($shop, $data);

            // Fulfillments
            case self::TOPIC_fulfillments_create:
            case self::TOPIC_fulfillments_update:
                return $this->storeFulfillment($shop, $data);

            case self::TOPIC_app_uninstalled:
                return $this->app_uninstall($shop, $data);

        }
        return response('Topic ignored');
    }

    function app_uninstall(ShopifyShop $shop, array $data)
    {
        if ($shop->user->admin == null)
            $shop->delete();
        return response("", 204);
    }

    function storeCheckout(ShopifyShop $shop, array $data)
    {
        $shopify_checkout = ShopifyCheckout::storeCheckout($shop, $data ?? []);
        $shopify_customer = ShopifyCustomer::storeCustomer($shop, $data['customer'] ?? []);
        $shopify_checkout->shopify_customer()->associate($shopify_customer);
        $shopify_checkout->save();

        return response('Checkout updated');
    }

    function storeOrder(ShopifyShop $shop, array $data, string $topic)
    {
        $shopify_order = ShopifyOrder::storeOrder($shop, $data);
        if (!$shopify_order) return response('');

        if ($topic == self::TOPIC_orders_create) $shopify_order->trigerAutomationOrderPlaced();
        if ($topic == self::TOPIC_orders_fulfilled) $shopify_order->trigerAutomationOrderFulfilled();

        if ($topic == self::TOPIC_orders_create && $shopify_order->from_emailwish) $shopify_order->triggerAdminAutomationEmailwishOrderPlaced();

        return response('Order updated');
    }

    function deleteOrder(ShopifyShop $shop, array $data)
    {
        $order = ShopifyOrder::findByShopAndId($shop, $data['id'] ?? '');
        if ($order) $order->delete();
        return response("", 204);
    }

    function storeFulfillment(ShopifyShop $shop, array $data)
    {
        $shopifyFulfillment = ShopifyFulfillment::storeFulfillment($shop, $data);
        if(!$shopifyFulfillment) return response('');
        $shopifyOrder = ShopifyOrder::findByShopAndId($shop, $shopifyFulfillment->getShopifyModel()->order_id);
        $shopifyCustomer = $shopifyOrder->shopify_customer;
        $shopifyFulfillment->shopify_order()->associate($shopifyOrder);
        $shopifyFulfillment->shopify_customer()->associate($shopifyCustomer);
        $shopifyFulfillment->save();

        $context = new ShopifyAutomationContext();
        $context->shopifyCustomer = $shopifyCustomer;
        $context->shopifyOrder = $shopifyOrder;
        $context->shopifyFulfillment = $shopifyFulfillment;

        if ($shopifyFulfillment->shopify_shipment_status == "delivered") {
            $automations = Automation2::findByTrigger($shop->customer, ShopifyAutomationContext::AUTOMATION_TRIGGER_SHOPIFY_ORDER_DELIVERED);
            foreach ($automations as $automation) {
                $automation->logger()->info(sprintf('Queuing automation "%s" in response to webhook', $automation->name));
                dispatch(new RunAutomationWithContext($automation, $context));
            }
        }
        return response('Fulfillment updated');
    }

    function storeProduct(ShopifyShop $shop, array $data)
    {
        ShopifyProduct::storeProduct($shop, $data ?? []);
        return response('Product updated');
    }

    function deleteProduct(ShopifyShop $shop, array $data)
    {
        $product = ShopifyProduct::findByShopAndId($shop, $data['id'] ?? '');
        if ($product) $product->delete();
        return response("", 204);
    }

    function storeCustomer(ShopifyShop $shop, array $data)
    {
        ShopifyCustomer::storeCustomer($shop, $data ?? []);
        return response('Customer updated');
    }

    function deleteCustomer(ShopifyShop $shop, array $data)
    {
        $customer = ShopifyCustomer::findByShopAndId($shop, $data['id'] ?? '');
        if ($customer) $customer->delete();
        return response("", 204);
    }

    function log_gdpr(string $name, array $data)
    {
        $filename = storage_path("gdpr/$name" . time() . '.json');
        $file = fopen($filename, 'w');
        fwrite($file, json_encode($data));
        fclose($file);
        return $filename;
    }

    function gdpr_customers_redact(ShopifyShop $shop, array $data)
    {
        $customer = ShopifyCustomer::findByShopAndId($shop, $data['customer']['id'] ?? null);
        $order_ids = $data['orders_to_redact'] ?? [];
        $orders = ShopifyOrder::findByShopAndIds($shop, $order_ids);

        $backup = [
            'shop' => $shop,
            'customer' => $customer,
            'orders' => $orders
        ];
        $this->log_gdpr('customers_redact', $backup);

        return response("", 204);
    }

    function gdpr_shop_redact(ShopifyShop $shop, array $data)
    {
        $backup = [
            'shop' => $shop,
            'products' => $shop->shopify_products,
            'customers' => $shop->shopify_customers,
            'orders' => $shop->shopify_orders
        ];
        $this->log_gdpr('shop_redact', $backup);

        return response("", 204);
    }

    function gdpr_customer_data_request(ShopifyShop $shop, array $data)
    {
        $customer = ShopifyCustomer::findByShopAndId($shop, $data['customer']['id'] ?? null);
        $customer_email = $data['customer']['email'] ?? "";
        $order_ids = $data['orders_requested'] ?? [];
        $orders = ShopifyOrder::findByShopAndIds($shop, $order_ids);

        $backup = [
            'shop' => $shop,
            'customer' => $customer,
            'orders' => $orders
        ];
        $this->log_gdpr('customer_data_request', $backup);

        $customer_data = json_decode($customer->data, true);
        $order_data = [];
        foreach ($orders as $order) {
            $order_data[] = json_decode($order->data, true);
        }

        $customer_data_file = $this->log_gdpr('DataRequest_Customer_', $customer_data);
        $order_data_file = $this->log_gdpr('DataRequest_Orders_', $order_data);

        Notification::route('mail', $customer_email)->notify(new GDPRDataRequest($customer_data_file, $order_data_file));

        return response("", 204);
    }
}
