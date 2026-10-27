<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderReceived extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Nouvelle commande {$this->order->reference}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line('Une nouvelle commande vient d\'arriver 🎉')
            ->line("Référence : **{$this->order->reference}**")
            ->line("Client : {$this->order->customer_name} ({$this->order->customer_email})")
            ->line("Total : " . number_format($this->order->total_amount, 2, ',', ' ') . ' €')
            ->line($this->order->delivery_type === 'delivery' ? 'Mode : Livraison' : 'Mode : Click & Collect')
            ->action('Voir la commande', url('/admin/commandes'))
            ->salutation('L\'équipe Douceur');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_order',
            'title' => "Nouvelle commande {$this->order->reference}",
            'message' => "{$this->order->customer_name} — " . number_format($this->order->total_amount, 2, ',', ' ') . ' €',
            'url' => '/admin/commandes',
            'icon' => '📦',
        ];
    }
}