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
            $table->foreignId('secondary_truck_id')->nullable()->after('truck_id')->constrained('trucks')->nullOnDelete();
            $table->string('secondary_vehicle_plate', 20)->nullable()->after('vehicle_plate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transport_guides', function (Blueprint $table) {
            $table->dropForeign(['secondary_truck_id']);
            $table->dropColumn(['secondary_truck_id', 'secondary_vehicle_plate']);
        });
    }
};
