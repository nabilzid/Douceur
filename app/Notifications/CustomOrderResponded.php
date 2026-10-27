<?php

namespace App\Notifications;

use App\Models\CustomOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomOrderResponded extends Notification
{
    use Queueable;

    public function __construct(public CustomOrder $customOrder) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $decision = $this->customOrder->status === 'accepted' ? 'accepté' : 'refusé';

        return (new MailMessage)
            ->subject("Le client a {$decision} le devis {$this->customOrder->reference}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Le client **{$this->customOrder->customer_name}** a {$decision} le devis.")
            ->line("Référence : **{$this->customOrder->reference}**")
            ->line("Montant du devis : " . number_format($this->customOrder->quoted_price, 2, ',', ' ') . ' €')
            ->action('Voir la demande', url('/admin/sur-mesure'))
            ->salutation("L'équipe Douceur");
    }

    public function toArray(object $notifiable): array
    {
        $decision = $this->customOrder->status === 'accepted' ? 'accepté' : 'refusé';

        return [
            'type' => 'custom_order_responded',
            'title' => "Devis {$decision} — {$this->customOrder->reference}",
            'message' => "{$this->customOrder->customer_name} a {$decision} le devis.",
            'url' => '/admin/sur-mesure',
            'icon' => $this->customOrder->status === 'accepted' ? '✅' : '❌',
        ];
    }
}