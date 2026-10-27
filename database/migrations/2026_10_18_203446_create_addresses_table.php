<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->string('recipient_name');
            $table->string('phone', 20)->nullable();
            $table->string('street');
            $table->string('street_2')->nullable();
            $table->string('postal_code', 10);
            $table->string('city');
            $table->string('country', 2)->default('FR');
            $table->boolean('is_default')->default(false);
            $table->enum('type', ['shipping', 'billing', 'both'])->default('both');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'is_default']);
        });
    }
    public function down(): void { Schema::dropIfExists('addresses'); }
};