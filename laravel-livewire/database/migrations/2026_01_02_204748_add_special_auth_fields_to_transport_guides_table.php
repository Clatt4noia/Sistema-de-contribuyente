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
            $table->string('special_auth_issuer')->nullable()->after('mtc_registration_number');
            $table->string('special_auth_number')->nullable()->after('special_auth_issuer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transport_guides', function (Blueprint $table) {
            $table->dropColumn(['special_auth_issuer', 'special_auth_number']);
        });
    }
};
