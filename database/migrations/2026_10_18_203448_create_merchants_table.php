<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('shop_name');
            $table->string('slug')->unique();
            $table->string('siret', 14)->nullable();
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('city')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('logo_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'suspended'])->default('pending');
            $table->boolean('is_active')->default(true);
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'is_active']);
        });
    }
    public function down(): void { Schema::dropIfExists('merchants'); }
};