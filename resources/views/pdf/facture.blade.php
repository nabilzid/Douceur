<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $invoiceNumber }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #3d2817; margin: 0; padding: 40px; }
        .header { border-bottom: 2px solid #c97b3f; padding-bottom: 15px; margin-bottom: 25px; }
        .header table { width: 100%; }
        .logo { font-size: 24px; font-weight: bold; color: #3d2817; }
        .logo span { color: #c97b3f; }
        .invoice-title { text-align: right; font-size: 28px; color: #c97b3f; font-weight: 300; letter-spacing: 2px; }
        .meta { text-align: right; font-size: 10px; color: #7a6a5c; line-height: 1.5; margin-top: 8px; }
        .client-box { background: #fdf8f0; padding: 12px; border-radius: 6px; margin-bottom: 20px; border-left: 3px solid #c97b3f; }
        .client-box h3 { font-size: 10px; text-transform: uppercase; color: #c97b3f; margin: 0 0 5px 0; }
        .client-box p { margin: 2px 0; }
        .table-items { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .table-items th { background: #3d2817; color: white; padding: 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
        .table-items td { padding: 8px; border-bottom: 1px solid #f5ebe0; }
        .table-items .right { text-align: right; }
        .totals { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .totals td { padding: 5px 8px; }
        .totals .label { text-align: right; color: #7a6a5c; width: 70%; }
        .totals .amount { text-align: right; font-weight: bold; width: 30%; }
        .totals .total-ttc td { border-top: 2px solid #c97b3f; padding-top: 8px; font-size: 13px; }
        .totals .total-ttc .amount { color: #c97b3f; }
        .mention { margin-top: 20px; font-size: 9px; color: #7a6a5c; line-height: 1.4; }
        .footer { margin-top: 30px; border-top: 1px solid #f5ebe0; padding-top: 10px; font-size: 8px; color: #7a6a5c; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td style="width: 60%;">
                    <div class="logo"><span>Douceur</span></div>
                    <div style="font-size: 9px; color: #7a6a5c; margin-top: 5px;">
                        12 rue de la Pâtisserie, 75011 Paris<br>
                        SIRET : 123 456 789 00010<br>
                        TVA Intra. : FR 00 123456789<br>
                        SAS au capital de 10 000 €
                    </div>
                </td>
                <td style="width: 40%;">
                    <div class="invoice-title">FACTURE</div>
                    <div class="meta">
                        <strong>N° :</strong> {{ $invoiceNumber }}<br>
                        <strong>Date :</strong> {{ now()->format('d/m/Y') }}<br>
                        @if($type === 'deposit')
                            <strong>Type :</strong> Facture d'acompte
                        @elseif($percentage === 100)
                            <strong>Type :</strong> Facture
                        @else
                            <strong>Type :</strong> Facture de solde
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="client-box">
        <h3>Client</h3>
        <p><strong>{{ $order->customer_name }}</strong></p>
        <p>{{ $order->customer_email }}</p>
        <p>{{ $order->customer_phone }}</p>
    </div>

    <p><strong>Prestation :</strong> {{ $order->eventLabel() }} — {{ $order->event_date->format('d/m/Y') }}</p>

    <table class="table-items">
        <thead>
            <tr>
                <th style="width: 50%;">Désignation</th>
                <th class="right" style="width: 15%;">Qté</th>
                <th class="right" style="width: 15%;">P.U. HT</th>
                <th class="right" style="width: 10%;">TVA</th>
                <th class="right" style="width: 10%;">Total TTC</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    @if($type === 'deposit')
                        Acompte {{ $percentage }} % — {{ $order->merchant_response }}
                    @elseif($percentage === 100)
                        {{ $order->merchant_response }}
                    @else
                        Solde — {{ $order->merchant_response }}
                    @endif
                </td>
                <td class="right">1</td>
                <td class="right">{{ number_format($order->quoted_price / 1.055, 2, ',', ' ') }} €</td>
                <td class="right">5,5 %</td>
                <td class="right">{{ number_format($order->quoted_price, 2, ',', ' ') }} €</td>
            </tr>
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="label">Total HT</td>
            <td class="amount">{{ number_format($order->quoted_price / 1.055, 2, ',', ' ') }} €</td>
        </tr>
        <tr>
            <td class="label">TVA (5,5 %)</td>
            <td class="amount">{{ number_format($order->quoted_price - ($order->quoted_price / 1.055), 2, ',', ' ') }} €</td>
        </tr>
        @if($type === 'deposit')
            {{-- Facture d'acompte (20 % ou 50 %) --}}
            <tr>
                <td class="label">Acompte versé ({{ $percentage }} %)</td>
                <td class="amount">{{ number_format($amountPaid, 2, ',', ' ') }} €</td>
            </tr>
            <tr class="total-ttc">
                <td class="label">Total TTC (acompte)</td>
                <td class="amount">{{ number_format($amountPaid, 2, ',', ' ') }} €</td>
            </tr>
        @else
            {{-- Facture de solde ou facture globale --}}
            <tr>
                <td class="label">Total TTC</td>
                <td class="amount">{{ number_format($order->quoted_price, 2, ',', ' ') }} €</td>
            </tr>

            @if($percentage === 100)
                {{-- Paiement intégral : pas d'acompte antérieur --}}
                <tr class="total-ttc">
                    <td class="label">Total réglé</td>
                    <td class="amount">{{ number_format($amountPaid, 2, ',', ' ') }} €</td>
                </tr>
            @else
                {{-- Paiement du solde après acompte partiel --}}
                <tr>
                    <td class="label">Acompte déjà versé</td>
                    <td class="amount">- {{ number_format($order->amount_paid - $amountPaid, 2, ',', ' ') }} €</td>
                </tr>
                <tr class="total-ttc">
                    <td class="label">Solde réglé</td>
                    <td class="amount">{{ number_format($amountPaid, 2, ',', ' ') }} €</td>
                </tr>
            @endif
        @endif
    </table>

    <div class="mention">
        <p>Date de règlement : à réception de la facture. Escompte pour paiement anticipé : néant.</p>
        <p>En cas de retard de paiement, pénalités de retard au taux légal en vigueur, et indemnité forfaitaire de 40 € pour frais de recouvrement.</p>
        <p>Garantie légale de conformité : 2 ans.</p>
    </div>

    <div class="footer">
        Douceur — SAS au capital de 10 000 € — SIRET 123 456 789 00010 — TVA FR 00 123456789<br>
        12 rue de la Pâtisserie, 75011 Paris
    </div>
</body>
</html>