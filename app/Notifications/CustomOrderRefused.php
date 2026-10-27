<?php

namespace App\Notifications;

use App\Models\CustomOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomOrderRefused extends Notification
{
    use Queueable;

    public function __construct(public CustomOrder $customOrder) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Votre demande sur mesure {$this->customOrder->reference} n’a pas pu être acceptée")
            ->greeting("Bonjour {$this->customOrder->customer_name},")
            ->line('Nous sommes désolés, votre demande sur mesure n’a pas pu être acceptée.')
            ->line("Motif : {$this->customOrder->merchant_response}")
            ->line('N’hésitez pas à nous contacter pour trouver une alternative.')
            ->salutation("L'équipe Douceur");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'custom_order_refused',
            'title' => "Demande refusée — {$this->customOrder->reference}",
            'message' => $this->customOrder->merchant_response,
            'url' => '/mes-demandes',
            'icon' => '❌',
        ];
    }
}