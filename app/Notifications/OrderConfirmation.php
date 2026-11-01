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
        // ✅ Toujours /suivi-commande, mais on ajoute le contact pour les invités
        $trackUrl = url('/suivi-commande?reference=' . urlencode($this->order->reference));

        // Si pas de compte client → on pré-remplit le contact
        if (! $this->order->user_id && $this->order->customer_email) {
            $trackUrl .= '&contact=' . urlencode($this->order->customer_email);
        }

        return (new MailMessage)
            ->subject("Votre commande {$this->order->reference} est confirmée")
            ->greeting("Bonjour {$this->order->customer_name},")
            ->line('Merci pour votre commande chez Douceur 🧁')
            ->line("Votre commande **{$this->order->reference}** a bien été enregistrée.")
            ->line("Total : " . number_format($this->order->total_amount, 2, ',', ' ') . ' €')
            ->line($this->order->delivery_type === 'delivery'
                ? 'Livraison prévue à l\'adresse : ' . $this->order->delivery_address
                : 'Retrait en boutique.')
            ->action('Suivre ma commande', $trackUrl)
            ->line('À très vite pour de nouvelles douceurs !')
            ->salutation('L\'équipe Douceur');
    }
}