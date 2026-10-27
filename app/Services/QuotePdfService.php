<?php

namespace App\Services;

use App\Models\CustomOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class QuotePdfService
{
    /**
     * Génère le PDF du devis, le stocke et retourne le chemin.
     */
    public function generate(CustomOrder $customOrder): string
    {
        $pdf = Pdf::loadView('pdf.devis', ['customOrder' => $customOrder])->setPaper('a4', 'portrait');
        $filename = 'devis-' . $customOrder->reference . '.pdf';
        $path = 'devis/' . $filename;
        Storage::disk('local')->put($path, $pdf->output());  // ✅ sauvegarde sur disque
        return $path;
    }

    public function getPdfContent(CustomOrder $customOrder): string
    {
        return Pdf::loadView('pdf.devis', ['customOrder' => $customOrder])->output();  // ✅ en mémoire
    }
}