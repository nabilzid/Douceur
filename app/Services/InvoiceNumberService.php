<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class InvoiceNumberService
{
    public function next(): string
    {
        return DB::transaction(function () {
            $sequence = DB::table('document_sequences')
                ->where('type', 'invoice')
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                DB::table('document_sequences')->insert([
                    'type' => 'invoice',
                    'last_number' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $sequence = DB::table('document_sequences')->where('type', 'invoice')->first();
            }

            $next = $sequence->last_number + 1;

            DB::table('document_sequences')
                ->where('type', 'invoice')
                ->update(['last_number' => $next, 'updated_at' => now()]);

            return 'FAC-' . now()->format('Y') . '-' . str_pad($next, 5, '0', STR_PAD_LEFT);
        });
    }
}