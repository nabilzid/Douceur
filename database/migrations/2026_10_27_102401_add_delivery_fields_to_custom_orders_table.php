<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->enum('delivery_type', ['delivery', 'pickup'])
                  ->default('pickup')
                  ->after('guests_count');

            $table->string('delivery_address')->nullable()->after('delivery_type');
            $table->string('delivery_postal_code', 10)->nullable()->after('delivery_address');
            $table->string('delivery_city')->nullable()->after('delivery_postal_code');
            $table->text('delivery_notes')->nullable()->after('delivery_city');
        });
    }

    public function down(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_type',
                'delivery_address',
                'delivery_postal_code',
                'delivery_city',
                'delivery_notes',
            ]);
        });
    }
};