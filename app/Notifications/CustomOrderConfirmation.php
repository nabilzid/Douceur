<?php

namespace App\Notifications;

use App\Models\CustomOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
class CustomOrderConfirmation extends Notification
{
    use Queueable;

    public function __construct(public CustomOrder $customOrder) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Votre demande sur mesure {$this->customOrder->reference} a bien été reçue")
            ->greeting("Bonjour {$this->customOrder->customer_name},")
            ->line('Merci pour votre demande sur mesure chez Douceur 🧁')
            ->line("Votre demande **{$this->customOrder->reference}** a bien été enregistrée.")
            ->line("Date de l'événement : " . $this->customOrder->event_date->format('d/m/Y'))
            ->line("Type d'événement : " . $this->customOrder->eventLabel())
            ->line("Description : {$this->customOrder->description}")
            ->line('Le pâtissier va étudier votre demande et vous répondre avec un devis personnalisé sous 48h.')
            ->action('Voir ma demande', url('/demande/' . $this->customOrder->reference . '?' . parse_url(URL::temporarySignedRoute('custom-orders.view', 
                        now()->addDays(7), ['reference' => $this->customOrder->reference]), PHP_URL_QUERY)))
            ->salutation('L\'équipe Douceur');
    }
}