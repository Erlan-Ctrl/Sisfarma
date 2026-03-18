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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->foreignId('store_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('reference', 80)->nullable();
            $table->string('status', 20)->default('posted'); // posted|voided (futuro)
            $table->string('payment_method', 20)->nullable(); // dinheiro|cartao|pix|outro
            $table->timestamp('occurred_at')->useCurrent();
            $table->text('notes')->nullable();

            $table->unsignedInteger('items_count')->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            $table->timestamps();

            $table->index(['store_id', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};

