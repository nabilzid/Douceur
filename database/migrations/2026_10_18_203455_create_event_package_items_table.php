<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('event_package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();
            $table->unique(['event_package_id', 'product_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('event_package_items'); }
};