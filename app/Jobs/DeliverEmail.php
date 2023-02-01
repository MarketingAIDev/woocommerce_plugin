<?php

namespace Acelle\Jobs;

use Acelle\Library\Automation\ShopifyAutomationContext;
use Acelle\Model\Email;
use Acelle\Model\Subscriber;

class DeliverEmail extends SystemJob
{
    /** @var Email $email */
    protected $email;
    /** @var Subscriber $subscriber */
    protected $subscriber;
    /** @var ShopifyAutomationContext $context */
    protected $context;
    protected $triggerId; // one email may be delivered to a given subscriber more than once (weekly recurring, birthday... for example)

    /**
     * Create a new job instance.
     * @note: Parent constructors are not called implicitly if the child class defines a constructor.
     *        In order to run a parent constructor, a call to parent::__construct() within the child constructor is required.
     *
     * @param Email $email
     * @param Subscriber $subscriber
     * @param integer $triggerId
     * @param ShopifyAutomationContext $context
     * @param integer $objectId
     * @param string $objectName
     */
    public function __construct($email, $subscriber, $triggerId, $context, $objectId, $objectName)
    {
        $this->email = $email;
        $this->subscriber = $subscriber;
        $this->triggerId = $triggerId;
        $this->context = $context;
        parent::__construct($objectId, $objectName);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->email->deliverTo($this->subscriber, $this->triggerId, $this->context);
    }
}
