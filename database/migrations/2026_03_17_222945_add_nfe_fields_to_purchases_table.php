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
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('nfe_key', 60)->nullable()->after('reference');
            $table->string('nfe_number', 20)->nullable()->after('nfe_key');
            $table->string('nfe_series', 10)->nullable()->after('nfe_number');
            $table->string('xml_hash', 64)->nullable()->after('nfe_series');
            $table->string('xml_path')->nullable()->after('xml_hash');

            $table->unique('nfe_key');
            $table->unique('xml_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropUnique(['nfe_key']);
            $table->dropUnique(['xml_hash']);

            $table->dropColumn(['nfe_key', 'nfe_number', 'nfe_series', 'xml_hash', 'xml_path']);
        });
    }
};
