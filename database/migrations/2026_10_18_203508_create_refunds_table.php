<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('reason')->nullable();
            $table->string('provider_reference')->nullable();
            $table->enum('status', ['pending', 'succeeded', 'failed'])->default('pending');
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('refunds'); }
};