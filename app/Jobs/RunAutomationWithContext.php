<?php

namespace Acelle\Jobs;

use Acelle\Library\Automation\ShopifyAutomationContext;
use Acelle\Model\Automation2;
use Acelle\Model\DebugLog;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;

class RunAutomationWithContext extends SystemJob implements ShouldQueue
{
    /** @var Automation2 */
    protected $automation;

    /** @var ShopifyAutomationContext */
    protected $context;

    public function __construct(Automation2 $automation, ShopifyAutomationContext $context)
    {
        $this->automation = $automation;
        $this->context = $context;
        parent::__construct();

        DebugLog::automation2Log("run_with_context", $this->automation->uid . ' ' . $this->automation->name, $context->toArray());
    }


    public function handle()
    {
        $this->automation->logger()->info(sprintf('Actually run automation "%s" in response to webhook', $this->automation->name));
        if ($this->context->shopifyCustomer) {
            $this->automation->initTrigger($this->context->shopifyCustomer->getOrCreateSubscriber(), $this->context->toExtraData());
            $this->automation->logger()->info('triggered for a single shopify customer');
            DebugLog::automation2Log("trigger_shopify_customer", $this->automation->uid . ' ' . $this->automation->name, $this->context->toArray());
        } elseif ($this->context->subscriber) {
            $this->automation->initTrigger($this->context->subscriber, $this->context->toExtraData());
            $this->automation->logger()->info('triggered for a single subscriber');
            DebugLog::automation2Log("trigger_subscriber", $this->automation->uid . ' ' . $this->automation->name, $this->context->toArray());
        } else {
            $subscribers = $this->automation->getNotTriggeredSubscribers();
            $this->automation->logger()->info('triggered for all customers');
            foreach ($subscribers as $subscriber) {
                $this->automation->initTrigger($subscriber, $this->context->toExtraData());
            }
            DebugLog::automation2Log("trigger_all_subscribers", $this->automation->uid . ' ' . $this->automation->name, $this->context->toArray());
        }
    }

    public function failed(Exception $exception)
    {
        $this->automation->logger()->info($exception->getMessage());
    }
}
