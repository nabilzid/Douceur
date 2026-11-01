<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCancelled extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Votre commande {$this->order->reference} a été annulée")
            ->greeting("Bonjour {$this->order->customer_name},")
            ->line("Votre commande **{$this->order->reference}** a été annulée.")
            ->line('')
            ->line('**Récapitulatif**')
            ->line("Total : " . number_format((float) $this->order->total_amount, 2, ',', ' ') . ' €');

        // Si la commande était payée, on le mentionne
        if ($this->order->payment_status === 'paid') {
            $mail->line('')
                ->line("💳 **Remboursement** : votre paiement de "
                    . number_format((float) $this->order->total_amount, 2, ',', ' ')
                    . ' € sera remboursé sous 5 à 10 jours ouvrés.')
                ->line("Pour toute question, contactez-nous à contact@douceur.fr.");
        }

        $mail->line('')
            ->line("Si vous avez des questions, n'hésitez pas à nous contacter.")
            ->action('Voir ma commande', url('/suivi-commande?reference=' . urlencode($this->order->reference) . ($this->order->user_id ? '' : '&contact=' . urlencode($this->order->customer_email))))
            ->line('')
            ->salutation("L'équipe Douceur");

        return $mail;
    }
}