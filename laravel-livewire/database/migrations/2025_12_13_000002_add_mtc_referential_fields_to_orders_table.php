<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'estimated_cost')) {
                $table->decimal('estimated_cost', 12, 2)->nullable()->after('estimated_duration_hours');
            }

            // Campos MTC (Anexo II)
            $table->decimal('referential_rate_sxtm', 12, 2)->nullable()->after('estimated_cost');
            $table->string('referential_route_key', 120)->nullable()->after('referential_rate_sxtm');
            $table->string('referential_route_dest', 160)->nullable()->after('referential_route_key');
            $table->string('referential_source', 50)->nullable()->after('referential_route_dest'); // DS-026-2024-MTC
            $table->unsignedSmallInteger('referential_year')->nullable()->after('referential_source'); // 2024
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $drop = [
                'estimated_cost',
                'referential_rate_sxtm',
                'referential_route_key',
                'referential_route_dest',
                'referential_source',
                'referential_year',
            ];
            foreach ($drop as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
