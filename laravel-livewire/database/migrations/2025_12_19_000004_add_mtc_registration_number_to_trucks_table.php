<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trucks', function (Blueprint $table) {
            if (! Schema::hasColumn('trucks', 'mtc_registration_number')) {
                $table->string('mtc_registration_number', 50)->nullable()->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trucks', function (Blueprint $table) {
            if (Schema::hasColumn('trucks', 'mtc_registration_number')) {
                $table->dropColumn('mtc_registration_number');
            }
        });
    }
};

