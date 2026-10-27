<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('delivery_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_zone_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('type', ['delivery', 'pickup']);
            $table->unsignedInteger('max_orders')->default(10);
            $table->unsignedInteger('current_orders')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['date', 'start_time', 'end_time', 'type', 'delivery_zone_id'], 'slot_unique');
            $table->index(['date', 'type', 'is_active']);
        });
    }
    public function down(): void { Schema::dropIfExists('delivery_slots'); }
};