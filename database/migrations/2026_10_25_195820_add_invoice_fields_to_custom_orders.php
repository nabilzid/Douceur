<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            // Facture d'acompte
            $table->string('deposit_invoice_number')->nullable()->after('amount_paid');
            $table->string('deposit_invoice_path')->nullable()->after('deposit_invoice_number');

            // Facture de solde (émise à la fin)
            $table->string('balance_invoice_number')->nullable()->after('deposit_invoice_path');
            $table->string('balance_invoice_path')->nullable()->after('balance_invoice_number');
        });
    }

    public function down(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->dropColumn([
                'deposit_invoice_number', 'deposit_invoice_path',
                'balance_invoice_number', 'balance_invoice_path',
            ]);
        });
    }
};