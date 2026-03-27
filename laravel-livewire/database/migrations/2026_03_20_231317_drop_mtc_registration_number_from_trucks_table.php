<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, transfer any existing mtc_registration_number data to tuce_number
        // where tuce_number is null to prevent data loss.
        DB::statement('UPDATE trucks SET tuce_number = mtc_registration_number WHERE tuce_number IS NULL AND mtc_registration_number IS NOT NULL');

        Schema::table('trucks', function (Blueprint $table) {
            if (Schema::hasColumn('trucks', 'mtc_registration_number')) {
                $table->dropColumn('mtc_registration_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trucks', function (Blueprint $table) {
            if (!Schema::hasColumn('trucks', 'mtc_registration_number')) {
                $table->string('mtc_registration_number', 50)->nullable()->after('type');
            }
        });
    }
};
