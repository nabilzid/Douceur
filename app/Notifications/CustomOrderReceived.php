<?php

namespace App\Notifications;

use App\Models\CustomOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomOrderReceived extends Notification
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
            ->subject("Nouvelle demande sur mesure {$this->customOrder->reference}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line('Une nouvelle demande sur mesure vous a été adressée 🎂')
            ->line("Référence : **{$this->customOrder->reference}**")
            ->line("Client : {$this->customOrder->customer_name} ({$this->customOrder->customer_email})")
            ->line("Type d'événement : " . $this->customOrder->eventLabel())
            ->line("Date de l'événement : " . $this->customOrder->event_date->format('d/m/Y'))
            ->line("Description : {$this->customOrder->description}")
            ->action('Voir la demande', url('/admin/sur-mesure'))
            ->salutation("L'équipe Douceur");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'custom_order',
            'title' => "Nouvelle demande sur mesure {$this->customOrder->reference}",
            'message' => "{$this->customOrder->customer_name} — {$this->customOrder->eventLabel()} — " . $this->customOrder->event_date->format('d/m/Y'),
            'url' => '/admin/sur-mesure',
            'icon' => '🎂',
        ];
    }
}