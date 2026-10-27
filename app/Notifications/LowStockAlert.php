<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification
{
    use Queueable;

    public function __construct(public Product $product) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("⚠️ Stock faible : {$this->product->name}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Le produit **{$this->product->name}** est en stock faible.")
            ->line("Stock actuel : **{$this->product->stock_quantity}** (seuil : {$this->product->low_stock_threshold})")
            ->line('Pensez à réapprovisionner pour éviter la rupture.')
            ->action('Gérer le stock', url('/admin/produits'))
            ->salutation("L'équipe Douceur");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'low_stock',
            'title' => "Stock faible : {$this->product->name}",
            'message' => "Stock actuel : {$this->product->stock_quantity} (seuil : {$this->product->low_stock_threshold})",
            'url' => '/admin/produits',
            'icon' => '⚠️',
        ];
    }
}