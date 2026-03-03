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
        Schema::table('transport_guides', function (Blueprint $table) {
            $table->string('payer_ruc', 11)->nullable()->after('destinatario_name');
            $table->string('payer_name', 100)->nullable()->after('payer_ruc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transport_guides', function (Blueprint $table) {
            $table->dropColumn(['payer_ruc', 'payer_name']);
        });
    }
};
