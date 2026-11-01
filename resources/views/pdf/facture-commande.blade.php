<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $invoiceNumber }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #3d2817;
            margin: 0;
            padding: 40px;
        }

        /* ── Header ── */
        .header {
            border-bottom: 2px solid #c97b3f;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header table { width: 100%; }
        .logo { font-size: 24px; font-weight: bold; color: #3d2817; }
        .logo span { color: #c97b3f; }
        .brand-info { font-size: 9px; color: #7a6a5c; margin-top: 5px; line-height: 1.6; }
        .invoice-title {
            text-align: right;
            font-size: 28px;
            color: #c97b3f;
            font-weight: 300;
            letter-spacing: 2px;
        }
        .meta {
            text-align: right;
            font-size: 10px;
            color: #7a6a5c;
            line-height: 1.6;
            margin-top: 8px;
        }

        /* ── Bloc client ── */
        .client-box {
            background: #fdf8f0;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 3px solid #c97b3f;
        }
        .client-box h3 {
            font-size: 10px;
            text-transform: uppercase;
            color: #c97b3f;
            margin: 0 0 5px 0;
        }
        .client-box p { margin: 2px 0; }

        /* ── Info commande ── */
        .order-info {
            margin-bottom: 15px;
            font-size: 10px;
            color: #7a6a5c;
        }
        .order-info strong { color: #3d2817; }

        /* ── Tableau articles ── */
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .table-items th {
            background: #3d2817;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table-items td {
            padding: 8px;
            border-bottom: 1px solid #f5ebe0;
        }
        .table-items .right { text-align: right; }
        .table-items .center { text-align: center; }

        /* ── Totaux ── */
        .totals {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .totals td { padding: 5px 8px; }
        .totals .label { text-align: right; color: #7a6a5c; width: 70%; }
        .totals .amount { text-align: right; font-weight: bold; width: 30%; }
        .totals .total-ttc td {
            border-top: 2px solid #c97b3f;
            padding-top: 8px;
            font-size: 13px;
        }
        .totals .total-ttc .amount { color: #c97b3f; }

        /* ── Mentions légales ── */
        .mention {
            margin-top: 20px;
            font-size: 9px;
            color: #7a6a5c;
            line-height: 1.5;
        }
        .mention p { margin: 3px 0; }

        /* ── Footer ── */
        .footer {
            margin-top: 30px;
            border-top: 1px solid #f5ebe0;
            padding-top: 10px;
            font-size: 8px;
            color: #7a6a5c;
            text-align: center;
            line-height: 1.6;
        }
    </style>
</head>
<body>

    {{-- ═══ HEADER ═══ --}}
    <div class="header">
        <table>
            <tr>
                <td style="width: 60%;">
                    <div class="logo"><span>Douceur</span></div>
                    <div class="brand-info">
                        12 rue des Gourmands, 13001 Marseille<br>
                        SIRET : 123 456 789 00012<br>
                        TVA Intra. : FR 12 123456789<br>
                        SAS au capital de 10 000 €
                    </div>
                </td>
                <td style="width: 40%;">
                    <div class="invoice-title">FACTURE</div>
                    <div class="meta">
                        <strong>N° :</strong> {{ $invoiceNumber }}<br>
                        <strong>Date :</strong> {{ $issuedAt->format('d/m/Y') }}<br>
                        <strong>Commande :</strong> {{ $order->reference }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ═══ CLIENT ═══ --}}
    <div class="client-box">
        <h3>Client</h3>
        <p><strong>{{ $order->customer_name }}</strong></p>
        <p>{{ $order->customer_email }}</p>
        @if($order->customer_phone)
            <p>{{ $order->customer_phone }}</p>
        @endif
        @if($order->delivery_address)
            <p>{{ $order->delivery_address }}</p>
            <p>{{ $order->delivery_postal_code }} {{ $order->delivery_city }}</p>
        @endif
    </div>

    {{-- ═══ INFO COMMANDE ═══ --}}
    <p class="order-info">
        <strong>Date de commande :</strong> {{ $order->created_at->format('d/m/Y') }}
        @if($order->delivery_type)
            — <strong>Mode :</strong>
            {{ $order->delivery_type === 'delivery' ? '🚚 Livraison' : '🏪 Click & Collect' }}
        @endif
    </p>

    {{-- ═══ ARTICLES ═══ --}}
    <table class="table-items">
        <thead>
            <tr>
                <th style="width: 50%;">Désignation</th>
                <th class="center" style="width: 15%;">Qté</th>
                <th class="right" style="width: 20%;">P.U. TTC</th>
                <th class="right" style="width: 15%;">Total TTC</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td class="center">{{ $item->quantity }}</td>
                    <td class="right">{{ number_format($item->unit_price, 2, ',', ' ') }} €</td>
                    <td class="right">{{ number_format($item->line_total, 2, ',', ' ') }} €</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ═══ TOTAUX ═══ --}}
    <table class="totals">
        <tr>
            <td class="label">Sous-total</td>
            <td class="amount">{{ number_format($order->subtotal, 2, ',', ' ') }} €</td>
        </tr>
        @if($order->delivery_fee > 0)
            <tr>
                <td class="label">Livraison</td>
                <td class="amount">{{ number_format($order->delivery_fee, 2, ',', ' ') }} €</td>
            </tr>
        @endif
        @if($order->discount_amount > 0)
            <tr>
                <td class="label">Remise</td>
                <td class="amount">- {{ number_format($order->discount_amount, 2, ',', ' ') }} €</td>
            </tr>
        @endif
        <tr class="total-ttc">
            <td class="label">Total TTC</td>
            <td class="amount">{{ number_format($order->total_amount, 2, ',', ' ') }} €</td>
        </tr>
    </table>

    {{-- ═══ MENTIONS LÉGALES ═══ --}}
    <div class="mention">
        <p><strong>Date de règlement :</strong> à réception de la facture. Escompte pour paiement anticipé : néant.</p>
        <p>En cas de retard de paiement, pénalités de retard au taux légal en vigueur, et indemnité forfaitaire de 40 € pour frais de recouvrement.</p>
        <p><strong>Garantie légale de conformité :</strong> 2 ans (article L217-3 du Code de la consommation).</p>
        <p>Les produits alimentaires frais sont exclus du droit de rétractation de 14 jours (article L221-28 du Code de la consommation).</p>
        <p>TVA 5,5 % sur les produits alimentaires à emporter.</p>
    </div>

    {{-- ═══ FOOTER ═══ --}}
    <div class="footer">
        Douceur — SAS au capital de 10 000 € — SIRET 123 456 789 00012 — TVA FR 12 123456789<br>
        12 rue des Gourmands, 13001 Marseille — Merci pour votre confiance ! 🍰
    </div>

</body>
</html>