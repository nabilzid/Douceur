<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->string('balance_payment_method', 20)
                  ->nullable()
                  ->after('balance_invoice_path');

            $table->timestamp('balance_paid_at')
                  ->nullable()
                  ->after('balance_payment_method');

            $table->timestamp('completed_at')
                  ->nullable()
                  ->after('balance_paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->dropColumn([
                'balance_payment_method',
                'balance_paid_at',
                'completed_at',
            ]);
        });
    }
};