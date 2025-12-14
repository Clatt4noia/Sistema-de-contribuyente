<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mtc_referential_rates', function (Blueprint $table) {
            $table->id();
            $table->string('source', 50); // 'DS-026-2024-MTC'
            $table->unsignedSmallInteger('year'); // 2024
            $table->string('route_key', 120); // e.g. 'LIMA-AGUAS-VERDES'
            $table->string('origin', 120); // 'Lima'
            $table->string('destination', 160); // 'Trujillo', 'Piura', etc
            $table->decimal('dv_partial_km', 10, 2)->nullable();
            $table->decimal('dv_acum_km', 10, 2)->nullable();
            $table->decimal('rate_soles_per_tm', 12, 2); // S/ x TM
            $table->timestamps();

            $table->unique(['year', 'route_key', 'destination'], 'mtc_rates_unique_year_route_dest');
            $table->index(['route_key', 'destination']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mtc_referential_rates');
    }
};
