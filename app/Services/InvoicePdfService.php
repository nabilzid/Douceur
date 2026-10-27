<?php

namespace App\Services;

use App\Models\CustomOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoicePdfService
{
    public function __construct(private InvoiceNumberService $numberService) {}

    public function generateDeposit(CustomOrder $order, float $amountPaid): string
    {
        $number = $this->numberService->next();

        $pdf = Pdf::loadView('pdf.facture', [
            'order' => $order,
            'invoiceNumber' => $number,
            'type' => 'deposit',
            'amountPaid' => $amountPaid,
            'remaining' => max(0, $order->quoted_price - ($order->amount_paid ?? 0)),
            'percentage' => $order->payment_percentage,
        ])->setPaper('a4', 'portrait');

        $path = 'factures/' . $number . '.pdf';
        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }

    public function generateBalance(CustomOrder $order, float $amountPaid): string
    {
        $number = $this->numberService->next();

        $pdf = Pdf::loadView('pdf.facture', [
            'order' => $order,
            'invoiceNumber' => $number,
            'type' => 'balance',
            'amountPaid' => $amountPaid,
            'remaining' => max(0, $order->quoted_price - ($order->amount_paid ?? 0)),
            'percentage' => $order->payment_percentage,
        ])->setPaper('a4', 'portrait');

        $path = 'factures/' . $number . '.pdf';
        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }
}