<?php

namespace Acelle\Events;

use Acelle\Events\Event;
use Acelle\Model\ShopifyOrder;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ShopifyOrderCreated extends Event
{
    /** @var ShopifyOrder $order */
    protected $order;

    /**
     * Create a new event instance.
     *
     * @param null $admin
     */
    public function __construct($admin = null)
    {
        $this->admin = $admin;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
