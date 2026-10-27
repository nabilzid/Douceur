<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('custom_order_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount_ht', 10, 2);
            $table->decimal('vat_amount', 10, 2);
            $table->decimal('amount_ttc', 10, 2);
            $table->string('pdf_path')->nullable();
            $table->timestamp('issued_at');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('invoices'); }
};