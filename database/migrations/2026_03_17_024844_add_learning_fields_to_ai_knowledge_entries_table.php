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
        Schema::table('ai_knowledge_entries', function (Blueprint $table) {
            $table->string('fingerprint', 40)->nullable()->unique();
            $table->string('source_type', 32)->nullable();
            $table->string('source_ref', 191)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_knowledge_entries', function (Blueprint $table) {
            $table->dropUnique(['fingerprint']);
            $table->dropColumn(['fingerprint', 'source_type', 'source_ref']);
        });
    }
};
