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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // in|out|adjust
            $table->string('type', 20);

            // Delta aplicado ao estoque. Ex.: +10 (entrada), -2 (saida), +5/-5 (ajuste).
            $table->integer('delta');

            $table->integer('quantity_before');
            $table->integer('quantity_after');

            $table->string('reason', 120)->nullable();
            $table->text('note')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['store_id', 'product_id', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};

