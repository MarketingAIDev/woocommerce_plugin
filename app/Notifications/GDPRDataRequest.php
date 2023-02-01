<?php

namespace Acelle\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class GDPRDataRequest extends Notification
{
    use Queueable;

    /**
     * @var string
     */
    private $customer_data_file;
    /**
     * @var string
     */
    private $order_data_file;

    /**
     * Create a new notification instance.
     *
     * @param string $customer_data_file
     * @param string $order_data_file
     */
    public function __construct(string $customer_data_file, string $order_data_file)
    {
        $this->customer_data_file = $customer_data_file;
        $this->order_data_file = $order_data_file;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("GDPR Data Request Response")
            ->line('Hello there')
            ->line('We received a data request on your behalf from Shopify.')
            ->line('')
            ->line('Please find the same attached with this email')
            ->attach($this->customer_data_file)
            ->attach($this->order_data_file);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
