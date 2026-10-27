<?php

namespace App\Notifications;

use App\Models\CustomOrder;
use App\Services\QuotePdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomOrderQuoted extends Notification
{
    use Queueable;

    public function __construct(public CustomOrder $customOrder) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $pdfService = new QuotePdfService();
        $pdfContent = $pdfService->getPdfContent($this->customOrder);

        $url = $this->customOrder->user_id
                ? url('/mes-demandes')
                : url('/suivi-demande?reference=' . $this->customOrder->reference);

        return (new MailMessage)
            ->subject('Votre devis pour la demande ' . $this->customOrder->reference)
            ->greeting('Bonjour ' . $this->customOrder->customer_name . ',')
            ->line('Votre patissier a repondu a votre demande sur mesure.')
            ->line('Devis propose : ' . number_format($this->customOrder->quoted_price, 2, ',', ' ') . ' EUR')
            ->line('Message : ' . $this->customOrder->merchant_response)
            ->action('Voir le devis', $url)
            ->line('Vous pouvez accepter ou refuser ce devis depuis votre espace.')
            ->attachData($pdfContent, 'devis-' . $this->customOrder->reference . '.pdf', [
                'mime' => 'application/pdf',
            ])
            ->salutation("L'equipe Douceur");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'custom_order_quoted',
            'title' => 'Devis envoye - ' . $this->customOrder->reference,
            'message' => number_format($this->customOrder->quoted_price, 2, ',', ' ') . ' EUR',
            'url' => '/mes-demandes',
            'icon' => 'money',
        ];
    }
}
