<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Devis {{ $customOrder->reference }}</title>
        <style>
            /* Polices : DomPDF supporte mal les polices Google, on utilise des fallbacks */
            body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #3d2817; margin: 0; padding: 0; background: #fff; }
            
            /* Couleurs de la charte Douceur */
            .text-caramel { color: #c97b3f; }
            .text-chocolate { color: #3d2817; }
            .text-muted { color: #7a6a5c; }
            .bg-cream { background-color: #fdf8f0; }
            .border-caramel { border-color: #c97b3f; }

            /* Structure principale */
            .page { padding: 40px 50px; }
            
            /* En-tête */
            .header { border-bottom: 2px solid #c97b3f; padding-bottom: 20px; margin-bottom: 30px; }
            .header-table { width: 100%; border-collapse: collapse; }
            .header-table td { vertical-align: top; }
            .logo { font-size: 28px; font-weight: bold; color: #3d2817; font-family: 'Playfair Display', serif; }
            .logo span { color: #c97b3f; }
            .shop-info { font-size: 10px; color: #7a6a5c; line-height: 1.4; margin-top: 5px; }
            
            .devis-title { text-align: right; }
            .devis-title h1 { font-size: 32px; color: #c97b3f; margin: 0; font-weight: 300; letter-spacing: 2px; }
            .devis-meta { font-size: 10px; color: #7a6a5c; margin-top: 10px; text-align: right; line-height: 1.6; }

            /* Bloc Client */
            .client-box { background: #fdf8f0; padding: 15px; border-radius: 8px; margin-bottom: 25px; border-left: 4px solid #c97b3f; }
            .client-box h3 { font-size: 12px; text-transform: uppercase; color: #c97b3f; margin: 0 0 5px 0; letter-spacing: 1px; }
            .client-box p { margin: 2px 0; font-size: 11px; }
            .client-name { font-weight: bold; color: #3d2817; font-size: 12px; }

            /* Détails événement */
            .event-details { margin-bottom: 25px; }
            .event-details h3 { font-size: 12px; text-transform: uppercase; color: #c97b3f; margin: 0 0 8px 0; letter-spacing: 1px; border-bottom: 1px solid #f5ebe0; padding-bottom: 4px; }
            .event-details p { margin: 3px 0; font-size: 11px; }
            .event-details strong { color: #3d2817; }

            /* Tableau */
            .table-items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            .table-items th { background: #3d2817; color: #fff; padding: 10px 8px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
            .table-items th.right { text-align: right; }
            .table-items td { padding: 10px 8px; border-bottom: 1px solid #f5ebe0; font-size: 11px; vertical-align: top; }
            .table-items td.right { text-align: right; }
            .table-items .desc { font-weight: bold; color: #3d2817; }
            .table-items .notes { font-size: 9px; color: #7a6a5c; margin-top: 2px; }

            /* Totaux */
            .totals-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            .totals-table td { padding: 6px 8px; font-size: 11px; }
            .totals-table .label { text-align: right; color: #7a6a5c; width: 70%; }
            .totals-table .amount { text-align: right; font-weight: bold; color: #3d2817; width: 30%; }
            .totals-table .total-ttc td { border-top: 2px solid #c97b3f; padding-top: 10px; font-size: 14px; }
            .totals-table .total-ttc .amount { color: #c97b3f; }

            /* Conditions */
            .conditions { margin-top: 30px; font-size: 9px; color: #7a6a5c; line-height: 1.5; }
            .conditions h4 { font-size: 10px; color: #3d2817; margin: 0 0 5px 0; text-transform: uppercase; }

            /* Footer */
            .footer { margin-top: 40px; border-top: 1px solid #f5ebe0; padding-top: 15px; font-size: 9px; color: #7a6a5c; text-align: center; line-height: 1.5; }
            .footer strong { color: #3d2817; }

            /* Signature */
            .signature-box { margin-top: 30px; border: 1px dashed #c97b3f; padding: 15px; border-radius: 8px; }
            .signature-box p { margin: 0 0 5px 0; font-size: 10px; color: #7a6a5c; }
            .signature-line { margin-top: 30px; border-top: 1px solid #7a6a5c; width: 200px; }
        </style>
    </head>
    <body>
        <div class="page">
            <!-- En-tête -->
            <div class="header">
                <table class="header-table">
                    <tr>
                        <td style="width: 60%;">
                            <div class="logo"><span>Douceur</span></div>
                            <div class="shop-info">
                                Pâtisseries artisanales<br>
                                12 rue de la Pâtisserie, 75011 Paris<br>
                                Tél : +33 1 23 45 67 89<br>
                                Email : contact@douceur.test<br>
                                SIRET : 123 456 789 00010<br>
                                TVA Intra. : FR 00 123456789
                            </div>
                        </td>
                        <td style="width: 40%;" class="devis-title">
                            <h1>DEVIS</h1>
                            <div class="devis-meta">
                                <strong>Référence :</strong> {{ $customOrder->reference }}<br>
                                <strong>Date d'établissement :</strong> {{ $customOrder->created_at->format('d/m/Y') }}<br>
                                <strong>Validité :</strong> 30 jours (jusqu'au {{ $customOrder->created_at->addDays(30)->format('d/m/Y') }})
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Client -->
            <div class="client-box">
                <h3>Client</h3>
                <p class="client-name">{{ $customOrder->customer_name }}</p>
                <p>{{ $customOrder->customer_email }}</p>
                <p>{{ $customOrder->customer_phone }}</p>
            </div>

            <!-- Détails de l'événement -->
            <div class="event-details">
                <h3>Détails de la prestation</h3>
                <p><strong>Type d'événement :</strong> {{ $customOrder->eventLabel() }}</p>
                <p><strong>Date de début (événement) :</strong> {{ $customOrder->event_date->format('d/m/Y') }}</p>
                @if($customOrder->guests_count)
                    <p><strong>Nombre de convives :</strong> {{ $customOrder->guests_count }} personnes</p>
                @endif
                <p><strong>Description :</strong> {{ $customOrder->description }}</p>
            </div>

            <!-- Tableau des prestations -->
            <table class="table-items">
                <thead>
                    <tr>
                        <th style="width: 50%;">Désignation</th>
                        <th class="right" style="width: 10%;">Qté</th>
                        <th class="right" style="width: 15%;">Prix unit. HT</th>
                        <th class="right" style="width: 10%;">TVA</th>
                        <th class="right" style="width: 15%;">Total TTC</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="desc">Prestation sur mesure</div>
                            <div class="notes">{{ $customOrder->merchant_response }}</div>
                        </td>
                        <td class="right">1</td>
                        <td class="right">{{ number_format($customOrder->quoted_price, 2, ',', ' ') }} €</td>
                        <td class="right">5,5 %</td>
                        <td class="right">{{ number_format($customOrder->quoted_price, 2, ',', ' ') }} €</td>
                    </tr>
                </tbody>
            </table>

            <!-- Totaux -->
            <table class="totals-table">
                <tr>
                    <td class="label">Total HT</td>
                    <td class="amount">{{ number_format($customOrder->quoted_price / 1.055, 2, ',', ' ') }} €</td>
                </tr>
                <tr>
                    <td class="label">TVA (5,5 %)</td>
                    <td class="amount">{{ number_format($customOrder->quoted_price - ($customOrder->quoted_price / 1.055), 2, ',', ' ') }} €</td>
                </tr>
                <tr class="total-ttc">
                    <td class="label">Total TTC</td>
                    <td class="amount">{{ number_format($customOrder->quoted_price, 2, ',', ' ') }} €</td>
                </tr>
            </table>

            <!-- Conditions -->
            <div class="conditions">
                <h4>Conditions de règlement</h4>
                <p>Paiement à réception de la facture. Un acompte de 30 % peut être demandé à la commande.</p>
                
                <h4>Conditions de livraison / retrait</h4>
                <p>Retrait en boutique ou livraison à l'adresse convenue. Les frais de livraison sont offerts à partir de 30 € d'achat.</p>
                
                <h4>Validité de l'offre</h4>
                <p>Ce devis est valable 30 jours à compter de sa date d'établissement. Passé ce délai, les prix indiqués pourront être révisés.</p>
            </div>

            <!-- Signature -->
            <div class="signature-box">
                <p><strong>Bon pour accord :</strong> Date et signature du client</p>
                <div class="signature-line"></div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <strong>Douceur</strong> — SAS au capital de 10 000 € — SIRET 123 456 789 00010 — TVA Intra. FR 00 123456789<br>
                Siège social : 12 rue de la Pâtisserie, 75011 Paris — contact@douceur.test — +33 1 23 45 67 89
            </div>
        </div>
    </body>
</html>