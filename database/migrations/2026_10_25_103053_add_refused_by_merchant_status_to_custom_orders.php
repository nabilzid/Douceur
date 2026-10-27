<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->enum('status', [
                'pending_quote', 'quoted', 'accepted', 'rejected',
                'refused_by_merchant', 'completed', 'cancelled',
            ])->default('pending_quote')->change();
        });
    }

    public function down(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->enum('status', [
                'pending_quote', 'quoted', 'accepted', 'rejected', 'completed', 'cancelled',
            ])->default('pending_quote')->change();
        });
    }
};