<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderConfirmation extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Votre commande {$this->order->reference} est confirmée")
            ->greeting("Bonjour {$this->order->customer_name},")
            ->line('Merci pour votre commande chez Douceur 🧁')
            ->line("Votre commande **{$this->order->reference}** a bien été enregistrée.")
            ->line("Total : " . number_format($this->order->total_amount, 2, ',', ' ') . ' €')
            ->line($this->order->delivery_type === 'delivery'
                ? 'Livraison prévue à l\'adresse : ' . $this->order->delivery_address
                : 'Retrait en boutique.')
            ->action('Suivre ma commande', url('/suivi-commande?reference=' . $this->order->reference))
            ->line('À très vite pour de nouvelles douceurs !')
            ->salutation('L\'équipe Douceur');
    }
}