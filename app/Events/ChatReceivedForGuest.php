<?php

namespace Acelle\Events;

use Acelle\Model\ChatMessage;
use Acelle\Model\ChatSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatReceivedForGuest implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $session;
    public $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ChatSession $session, ChatMessage $message)
    {
        $this->session = $session;
        $this->message = $message;
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'chat.received.for.guest';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $id = $this->session->id;
        $key = $this->session->secret_key;
        return new Channel("chat_session.$id.$key.notifications");
    }
}
