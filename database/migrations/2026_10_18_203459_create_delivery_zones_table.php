<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('postal_codes'); // ["75001","75002",...] ou ["75*"]
            $table->decimal('delivery_fee', 8, 2)->default(0);
            $table->decimal('free_delivery_threshold', 8, 2)->nullable(); // ex: 20€
            $table->unsignedInteger('min_order_amount')->default(0);
            $table->unsignedInteger('estimated_delivery_minutes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('delivery_zones'); }
};