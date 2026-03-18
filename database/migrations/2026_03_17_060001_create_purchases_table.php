<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();

            $table->foreignId('store_id')->constrained()->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('reference', 80)->nullable();
            $table->string('status', 20)->default('posted'); // posted|voided (futuro)
            $table->timestamp('occurred_at')->useCurrent();
            $table->text('notes')->nullable();

            $table->unsignedInteger('items_count')->default(0);
            $table->decimal('total_cost', 12, 2)->default(0);

            $table->timestamps();

            $table->index(['store_id', 'occurred_at']);
            $table->index(['supplier_id', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};

