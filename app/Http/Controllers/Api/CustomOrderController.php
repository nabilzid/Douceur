<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomOrder;
use App\Models\Merchant;
use App\Models\Setting;
use App\Notifications\CustomOrderConfirmation;
use App\Notifications\CustomOrderReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class CustomOrderController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        $rules = [
            'merchant_id' => 'required|exists:merchants,id',
            'event_date' => 'required|date|after_or_equal:today',
            'event_type' => 'required|in:birthday,wedding,baptism,corporate,other',
            'guests_count' => 'nullable|integer|min:1',
            'delivery_type' => 'required|in:delivery,pickup',
            'delivery_address' => 'required_if:delivery_type,delivery|nullable|string|max:255',
            'delivery_postal_code' => 'required_if:delivery_type,delivery|nullable|string|max:10',
            'delivery_city' => 'required_if:delivery_type,delivery|nullable|string|max:255',
            'delivery_notes' => 'nullable|string|max:1000',
            'description' => 'required|string|min:10',
        ];

        if (! $user) {
            $rules['customer_name'] = 'required|string|max:255';
            $rules['customer_email'] = 'required|email';
            $rules['customer_phone'] = 'required|string|max:20';
        }

        $validated = $request->validate($rules);

        $minLead = (int) Setting::get('custom_order.min_lead_days', 3);
        $eventDate = \Carbon\Carbon::parse($validated['event_date'])->startOfDay();
        $minDate = now()->addDays($minLead)->startOfDay();

        if ($eventDate->lt($minDate)) {
            abort(422, "La date de l'événement doit être au minimum à J+{$minLead}.");
        }

        $customerName = $user?->name ?? $validated['customer_name'];
        $customerEmail = $user?->email ?? $validated['customer_email'];
        $customerPhone = $user?->phone ?? $validated['customer_phone'];

        $order = CustomOrder::create([
            'reference' => 'SUR-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
            'user_id' => $user?->id,
            'merchant_id' => $validated['merchant_id'],
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'event_date' => $validated['event_date'],
            'event_type' => $validated['event_type'],
            'guests_count' => $validated['guests_count'] ?? null,
            'delivery_type' => $validated['delivery_type'],
            'delivery_address' => $validated['delivery_address'] ?? null,
            'delivery_postal_code' => $validated['delivery_postal_code'] ?? null,
            'delivery_city' => $validated['delivery_city'] ?? null,
            'delivery_notes' => $validated['delivery_notes'] ?? null,
            'description' => $validated['description'],
            'status' => 'pending_quote',
        ]);

        $recipients = collect([\App\Models\Merchant::find($order->merchant_id)?->user])
            ->merge(\App\Models\User::where('role', 'admin')->get())
            ->filter()
            ->unique('id');

        $recipients->each(fn ($recipient) => $recipient->notify(new CustomOrderReceived($order)));

        return response()->json([
            'custom_order' => $order,
            'message' => 'Votre demande a été envoyée. Le pâtissier vous répondra rapidement.',
        ], 201);
    }

    public function mine(Request $request)
    {
        return $request->user()
            ->customOrders()
            ->with(['merchant'])
            ->latest()
            ->get();
    }

    public function show(string $reference)
    {
        return CustomOrder::where('reference', $reference)
            ->with(['merchant', 'items'])
            ->firstOrFail();
    }

    public function respond(Request $request, string $reference)
    {
        $customOrder = CustomOrder::where('reference', $reference)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($customOrder->status !== 'quoted') {
            abort(422, 'Cette demande n’est pas en attente de votre décision.');
        }

        $validated = $request->validate([
            'decision' => 'required|in:accept,reject',
            'payment_percentage' => 'required_if:decision,accept|in:20,50,100',
        ]);

        // ✅ Refus : statut immédiat
        if ($validated['decision'] === 'reject') {
            $customOrder->update(['status' => 'rejected']);
            $this->notifyCustomOrderResponded($customOrder);
            return response()->json(['status' => 'rejected']);
        }

        // ✅ Acceptation : créer la session Stripe SANS changer le statut
        $percentage = (int) $validated['payment_percentage'];
        $amount = round($customOrder->quoted_price * $percentage / 100, 2);

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::create([
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => "Acompte {$percentage}% — Devis {$customOrder->reference}",
                    ],
                    'unit_amount' => (int) round($amount * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => url('/mes-demandes?payment=success&reference=' . $customOrder->reference),
            'cancel_url' => url('/mes-demandes?payment=cancel&reference=' . $customOrder->reference),
            'metadata' => [
                'custom_order_id' => $customOrder->id,
                'reference' => $customOrder->reference,
                'payment_percentage' => $percentage,
            ],
            'customer_email' => $customOrder->customer_email,
        ]);

        return response()->json([
            'status' => 'pending_payment',
            'checkout_url' => $session->url,
        ]);
    }

    public function track(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string',
            'contact' => 'nullable|string',
        ]);

        if ($request->user()) {
            $customOrder = CustomOrder::where('reference', $validated['reference'])
                ->where('user_id', $request->user()->id)
                ->with('merchant')
                ->first();

            if ($customOrder) {
                return response()->json($this->formatCustomOrder($customOrder));
            }
        }

        if (! $validated['contact']) {
            abort(422, 'Veuillez fournir un email ou téléphone.');
        }

        $customOrder = CustomOrder::where('reference', $validated['reference'])
            ->where(function ($q) use ($validated) {
                $q->where('customer_email', $validated['contact'])
                  ->orWhere('customer_phone', $validated['contact']);
            })
            ->with('merchant')
            ->first();

        if (! $customOrder) {
            abort(404, 'Aucune demande trouvée avec ces informations.');
        }

        return response()->json($this->formatCustomOrder($customOrder));
    }

    private function formatCustomOrder(CustomOrder $order): array
    {
        $data = [
            'reference' => $order->reference,
            'status' => $order->status,
            'event_date' => $order->event_date,
            'event_type' => $order->event_type,
            'event_label' => $order->eventLabel(),
            'description' => $order->description,
            'guests_count' => $order->guests_count,
            'delivery_type' => $order->delivery_type,
            'delivery_address' => $order->delivery_address,
            'delivery_postal_code' => $order->delivery_postal_code,
            'delivery_city' => $order->delivery_city,
            'delivery_notes' => $order->delivery_notes,
            'quoted_price' => $order->quoted_price,
            'amount_paid' => (float) ($order->amount_paid ?? 0),
            'payment_percentage' => $order->payment_percentage,
            'merchant_response' => $order->merchant_response,
            'created_at' => $order->created_at,
            'quote_pdf_url' => null,
        ];

        if ($order->quote_pdf_path) {
            $data['quote_pdf_url'] = URL::signedRoute(
                'custom-orders.quote-pdf',
                ['reference' => $order->reference]
            );
        }

        if ($order->deposit_invoice_path) {
            $data['deposit_invoice_url'] = URL::signedRoute('custom-orders.invoice', [
                'reference' => $order->reference,
                'type' => 'deposit',
            ]);
        }

        if ($order->balance_invoice_path) {
            $data['balance_invoice_url'] = URL::signedRoute('custom-orders.invoice', [
                'reference' => $order->reference,
                'type' => 'balance',
            ]);
        }

        return $data;
    }

    public function downloadQuotePdf(Request $request, string $reference)
    {
        $customOrder = CustomOrder::where('reference', $reference)->firstOrFail();

        $isOwner = $customOrder->user_id && $request->user()?->id === $customOrder->user_id;
        $isSigned = $request->hasValidSignature();
        $isAdmin = $request->user()?->role === 'admin';

        if (! $isOwner && ! $isSigned && ! $isAdmin) {
            abort(403, 'Accès non autorisé à ce devis.');
        }

        if (! $customOrder->quote_pdf_path || ! Storage::disk('local')->exists($customOrder->quote_pdf_path)) {
            abort(404, 'Devis introuvable.');
        }

        $fullPath = Storage::disk('local')->path($customOrder->quote_pdf_path);

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="devis-' . $customOrder->reference . '.pdf"',
        ]);
    }

    public function respondAsGuest(Request $request, string $reference)
    {
        $validated = $request->validate([
            'contact' => 'required|string',
            'decision' => 'required|in:accept,reject',
            'payment_percentage' => 'required_if:decision,accept|in:20,50,100',
        ]);

        $customOrder = CustomOrder::where('reference', $reference)
            ->where(function ($q) use ($validated) {
                $q->where('customer_email', $validated['contact'])
                  ->orWhere('customer_phone', $validated['contact']);
            })
            ->firstOrFail();

        if ($customOrder->status !== 'quoted') {
            abort(422, 'Cette demande n’est pas en attente de décision.');
        }

        if ($validated['decision'] === 'reject') {
            $customOrder->update(['status' => 'rejected']);
            $this->notifyCustomOrderResponded($customOrder);
            return response()->json(['status' => 'rejected']);
        }

        $percentage = (int) $validated['payment_percentage'];
        $amount = round($customOrder->quoted_price * $percentage / 100, 2);

        Stripe::setApiKey(config('services.stripe.secret'));

        // ✅ Passer le contact dans les URLs de retour Stripe
        $contactEncoded = urlencode($validated['contact']);

        $session = Session::create([
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => "Acompte {$percentage}% — Devis {$customOrder->reference}",
                    ],
                    'unit_amount' => (int) round($amount * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => url('/suivi-demande?reference=' . $customOrder->reference . '&payment=success&contact=' . $contactEncoded),
            'cancel_url' => url('/suivi-demande?reference=' . $customOrder->reference . '&payment=cancel&contact=' . $contactEncoded),
            'metadata' => [
                'custom_order_id' => $customOrder->id,
                'reference' => $customOrder->reference,
                'payment_percentage' => $percentage,
            ],
            'customer_email' => $customOrder->customer_email,
        ]);

        return response()->json([
            'status' => 'pending_payment',
            'checkout_url' => $session->url,
        ]);
    }

    private function notifyCustomOrderResponded(CustomOrder $customOrder): void
    {
        $recipients = collect([\App\Models\Merchant::find($customOrder->merchant_id)?->user])
            ->merge(\App\Models\User::where('role', 'admin')->get())
            ->filter()
            ->unique('id');

        $recipients->each(fn ($recipient) => $recipient->notify(
            new \App\Notifications\CustomOrderResponded($customOrder)
        ));
    }

    public function downloadInvoice(Request $request, string $reference, string $type)
    {
        $customOrder = CustomOrder::where('reference', $reference)->firstOrFail();

        $isOwner = $customOrder->user_id && $request->user()?->id === $customOrder->user_id;
        $isSigned = $request->hasValidSignature();
        $isAdmin = $request->user()?->role === 'admin';

        if (! $isOwner && ! $isSigned && ! $isAdmin) {
            abort(403);
        }

        $path = $type === 'deposit' ? $customOrder->deposit_invoice_path : $customOrder->balance_invoice_path;

        if (! $path || ! Storage::disk('local')->exists($path)) {
            abort(404, 'Facture introuvable.');
        }

        return response()->file(Storage::disk('local')->path($path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }
}