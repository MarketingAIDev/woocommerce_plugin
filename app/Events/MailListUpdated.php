<?php

namespace Acelle\Events;

use Acelle\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MailListUpdated extends Event
{
    public $mailList;
    public $delayed;

    /**
     * Create a new event instance.
     *
     * @param $mailList
     * @param bool $delayed
     */
    public function __construct($mailList, $delayed = true)
    {
        $this->mailList = $mailList;
        $this->delayed = $delayed;
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
