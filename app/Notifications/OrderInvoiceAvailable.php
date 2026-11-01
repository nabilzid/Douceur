<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class OrderInvoiceAvailable extends Notification
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
            ->subject("Votre commande {$this->order->reference} est livrée — facture {$this->order->invoice_number} disponible")
            ->greeting("Bonjour {$this->order->customer_name},")
            ->line("🎉 Votre commande **{$this->order->reference}** a bien été livrée.")
            ->line('')
            ->line("📦 **Récapitulatif**")
            ->line("Total : " . number_format((float) $this->order->total_amount, 2, ',', ' ') . ' €')
            ->line("Facture : {$this->order->invoice_number}")
            ->line('')
            ->line("🧾 Votre facture est disponible en pièce jointe de cet email pour vos archives.");

        // Facture en PJ
        if ($this->order->invoice_path && Storage::disk('local')->exists($this->order->invoice_path)) {
            $mail->attach(Storage::disk('local')->path($this->order->invoice_path), [
                'as' => "facture-{$this->order->invoice_number}.pdf",
                'mime' => 'application/pdf',
            ]);
        }

        // URL vers le suivi de commande (page client)
        $trackUrl = url('/suivi-commande?reference=' . urlencode($this->order->reference)
            . '&contact=' . urlencode($this->order->customer_email));

        // URL signée vers la facture (lien de secours)
        $invoiceUrl = URL::signedRoute('orders.invoice', [
            'reference' => $this->order->reference,
        ]);

        return $mail
            ->action('Voir ma commande', $trackUrl)
            ->line('')
            ->line("Vous pouvez aussi télécharger votre facture directement : [{$invoiceUrl}]({$invoiceUrl})")
            ->line('')
            ->salutation("Merci pour votre confiance ! 🍰\nL'équipe Douceur");
    }
}