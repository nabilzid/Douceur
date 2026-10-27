<?php

namespace App\Notifications;

use App\Models\CustomOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
class CustomOrderStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public CustomOrder $customOrder) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status = $this->customOrder->status;

        $subject = match ($status) {
            'completed' => "Votre commande sur mesure {$this->customOrder->reference} est terminée",
            'cancelled' => "Votre demande sur mesure {$this->customOrder->reference} a été annulée",
            default => "Mise à jour de votre demande {$this->customOrder->reference}",
        };

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting("Bonjour {$this->customOrder->customer_name},");

        if ($status === 'completed') {
            $message->line('Votre commande sur mesure est terminée 🎉')
                ->line('Merci de votre confiance, à très vite !');
        } elseif ($status === 'cancelled') {
            $message->line('Votre demande sur mesure a été annulée.')
                ->line($this->customOrder->merchant_response
                    ? "Motif : {$this->customOrder->merchant_response}"
                    : 'N\'hésitez pas à nous contacter pour plus d\'informations.');
        }

        return $message
            ->action('Voir ma demande', url('/demande/' . $this->customOrder->reference . '?' . parse_url(
                URL::temporarySignedRoute('custom-orders.view', now()->addDays(7), ['reference' => $this->customOrder->reference]),
                PHP_URL_QUERY
            )))
            ->salutation('L\'équipe Douceur');
    }
}