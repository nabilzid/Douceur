<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('custom_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();

            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 20);

            $table->date('event_date');
            $table->enum('event_type', ['birthday', 'wedding', 'baptism', 'corporate', 'other']);
            $table->unsignedInteger('guests_count')->nullable();
            $table->text('description');
            $table->decimal('budget', 10, 2)->nullable();

            $table->decimal('quoted_price', 10, 2)->nullable();
            $table->text('merchant_response')->nullable();
            $table->enum('status', ['pending_quote', 'quoted', 'accepted', 'rejected', 'completed', 'cancelled'])->default('pending_quote');

            $table->timestamps();
            $table->softDeletes();
            $table->index(['merchant_id', 'status']);
            $table->index('event_date');
        });
    }
    public function down(): void { Schema::dropIfExists('custom_orders'); }
};