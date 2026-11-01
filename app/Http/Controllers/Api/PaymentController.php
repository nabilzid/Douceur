<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;
use App\Events\AdminNotificationCreated;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
class PaymentController extends Controller
{
    public function checkout(Request $request, string $reference)
    {
        $order = Order::where('reference', $reference)
        ->where('payment_status', 'pending')
        ->firstOrFail();

        // Si la commande a un user_id, vérifier que c'est bien le même utilisateur (si connecté)
        if ($order->user_id !== null && $request->user()?->id !== $order->user_id) {
            abort(403, 'Cette commande ne vous appartient pas.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $lineItems = $order->items->map(fn ($item) => [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => ['name' => $item->product_name],
                'unit_amount' => (int) round($item->unit_price * 100),
            ],
            'quantity' => $item->quantity,
        ])->toArray();

        // Ajouter les frais de livraison si nécessaire
        if ($order->delivery_fee > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => ['name' => 'Frais de livraison'],
                    'unit_amount' => (int) round($order->delivery_fee * 100),
                ],
                'quantity' => 1,
            ];
        }

        $session = Session::create([
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => config('app.url') . '/commande/succes?session_id={CHECKOUT_SESSION_ID}&order=' . $order->reference,
            'cancel_url' => config('app.url') . '/commande/annule?order=' . $order->reference,
            'metadata' => ['order_id' => $order->id, 'reference' => $order->reference],
            'customer_email' => $order->customer_email,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'stripe',
            'provider_reference' => $session->id,
            'amount' => $order->total_amount,
            'currency' => 'EUR',
            'status' => 'pending',
            'provider_payload' => $session->toArray(),
        ]);

        return response()->json(['checkout_url' => $session->url]);
    }

    public function status(string $reference)
    {
        $order = Order::where('reference', $reference)->firstOrFail();

        return response()->json([
            'payment_status' => $order->payment_status,
            'order_status' => $order->status,
        ]);
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type !== 'checkout.session.completed') {
            return response()->json(['received' => true]);
        }

        $session = $event->data->object;

        try {
            DB::transaction(function () use ($session) {

                // ============================================================
                // CAS 1 : Paiement d'un devis sur mesure (acompte ou totalité)
                // ============================================================
                if (isset($session->metadata->custom_order_id) && $session->payment_status === 'paid') {

                    $customOrder = \App\Models\CustomOrder::where('id', $session->metadata->custom_order_id)
                        ->lockForUpdate()
                        ->first();

                    if ($customOrder && $customOrder->status === 'quoted') {
                        $amountPaid = $session->amount_total / 100;
                        $percentage = (int) ($session->metadata->payment_percentage ?? 0);

                        $customOrder->update([
                            'status' => 'accepted',
                            'amount_paid' => $customOrder->amount_paid + $amountPaid,
                            'payment_percentage' => $percentage,
                        ]);

                        $invoiceService = new \App\Services\InvoicePdfService(new \App\Services\InvoiceNumberService());

                        if ($percentage < 100) {
                            $path = $invoiceService->generateDeposit($customOrder, $amountPaid);
                            $customOrder->update(['deposit_invoice_path' => $path]);
                        } else {
                            $path = $invoiceService->generateBalance($customOrder, $amountPaid);
                            $customOrder->update(['balance_invoice_path' => $path]);
                        }

                        $recipients = collect([\App\Models\Merchant::find($customOrder->merchant_id)?->user])
                            ->merge(\App\Models\User::where('role', 'admin')->get())
                            ->filter()
                            ->unique('id');

                        $recipients->each(fn ($recipient) => $recipient->notify(
                            new \App\Notifications\CustomOrderResponded($customOrder)
                        ));

                        // ✅ NOUVEAU : Reverb broadcast
                        AdminNotificationCreated::dispatch([
                            'type' => 'custom_order_paid',
                            'title' => "Devis {$customOrder->reference} payé",
                            'message' => "{$customOrder->customer_name} — " . number_format($amountPaid, 2, ',', ' ') . ' €',
                            'url' => '/admin/sur-mesure',
                            'icon' => '💳',
                        ]);
                    }

                    return;
                }

                // ============================================================
                // CAS 2 : Paiement d'une commande standard
                // ============================================================
                $orderId = $session->metadata->order_id ?? null;

                if (! $orderId) {
                    return;
                }

                $order = Order::where('id', $orderId)->lockForUpdate()->first();

                if (! $order || $order->payment_status !== 'pending') {
                    return;
                }

                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed',
                ]);

                Payment::where('order_id', $order->id)
                    ->where('provider_reference', $session->id)
                    ->update([
                        'status' => 'succeeded',
                        'paid_at' => now(),
                    ]);

                Notification::route('mail', $order->customer_email)
                    ->notify(new \App\Notifications\OrderConfirmation($order));

                $merchant = \App\Models\Merchant::find($order->merchant_id);
                if ($merchant && $merchant->user) {
                    $merchant->user->notify(new \App\Notifications\NewOrderReceived($order));
                }

                \App\Models\User::where('role', 'admin')->get()->each(
                    fn ($admin) => $admin->notify(new \App\Notifications\NewOrderReceived($order))
                );

                // ✅ NOUVEAU : Reverb broadcast
                AdminNotificationCreated::dispatch([
                    'type' => 'new_order',
                    'title' => "Nouvelle commande {$order->reference}",
                    'message' => "{$order->customer_name} — " . number_format($order->total_amount, 2, ',', ' ') . ' €',
                    'url' => '/admin/commandes',
                    'icon' => '📦',
                ]);
            });
        } catch (\Throwable $e) {
            \Log::error('Stripe webhook processing failed', [
                'event_id' => $event->id ?? null,
                'type' => $event->type ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['received' => true, 'error' => 'processing_failed'], 200);
        }

        return response()->json(['received' => true]);
    }
}