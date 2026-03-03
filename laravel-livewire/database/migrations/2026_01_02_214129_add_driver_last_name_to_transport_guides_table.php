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
            $table->string('driver_last_name')->nullable()->after('driver_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transport_guides', function (Blueprint $table) {
            $table->dropColumn('driver_last_name');
        });
    }
};
