<?php
namespace Acelle\Http\Controllers;
use Illuminate\Http\Request;
use Acelle\Model\ShopifyFulfillment;

use Acelle\Helpers\ShopifyHelper;
use Acelle\Jobs\RunAutomationWithContext;
use Acelle\Library\Automation\ShopifyAutomationContext;
use Acelle\Model\Automation2;
use Acelle\Model\ShopifyCheckout;
use Acelle\Model\ShopifyCustomer;
use Acelle\Model\ShopifyOrder;
use Acelle\Model\ShopifyProduct;
use Acelle\Model\ShopifyShop;
use Acelle\Model\ShopifyWebhookLog;
use Acelle\Notifications\GDPRDataRequest;
use Illuminate\Support\Facades\Notification;

class TrackingFulfillmentController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function changeInTrackingStatus(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            $isDelivered = true;
            foreach ($data['trackers'] as $t) {
                if($t['last'] && $t['last']['status'] == 'DELIVERED') {
                    $isDelivered = $isDelivered && true;
                } else {
                    $isDelivered = $isDelivered && false;
                }
            }
            if($isDelivered) {
                $fulfillment = ShopifyFulfillment::find($data['id']);
                if($fulfillment) {
                    $fulfillment->setFulfillmentStatus('delivered');

                    $context = new ShopifyAutomationContext();
                    $context->shopifyCustomer = $fulfillment->shopify_customer;
                    $context->shopifyOrder = $fulfillment->shopify_order;
                    $context->shopifyFulfillment = $fulfillment;
                    $shop = ShopifyShop::find($fulfillment->shop_id);
                    if($shop) {
                        $automations = Automation2::findByTrigger($shop->customer, ShopifyAutomationContext::AUTOMATION_TRIGGER_SHOPIFY_ORDER_DELIVERED);

                        foreach ($automations as $automation) {
                            $automation->logger()->info(sprintf('Queuing automation "%s" in response to webhook', $automation->name));
                            dispatch(new RunAutomationWithContext($automation, $context));
                        }
                    }
                }

                return response('DELIVERED', 200);
            }
            return response('Not DELIVERED', 200);
        } else {
            return response('Method Not Allowed', 405);
        }
    }
}