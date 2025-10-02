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
        Schema::table('trucks', function (Blueprint $table) {
            if (! Schema::hasColumn('trucks', 'maintenance_interval_days')) {
                $table->unsignedSmallInteger('maintenance_interval_days')->default(90)->after('technical_details');
            }

            if (! Schema::hasColumn('trucks', 'maintenance_mileage_threshold')) {
                $table->unsignedInteger('maintenance_mileage_threshold')->default(10000)->after('maintenance_interval_days');
            }

            if (! Schema::hasColumn('trucks', 'last_maintenance_mileage')) {
                $table->unsignedBigInteger('last_maintenance_mileage')->default(0)->after('maintenance_mileage_threshold');
            }
        });

        Schema::create('driver_trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('provider')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->unsignedSmallInteger('hours')->nullable();
            $table->string('status')->default('valid');
            $table->string('certificate_url')->nullable();
            $table->timestamps();
        });

        Schema::table('maintenances', function (Blueprint $table) {
            if (! Schema::hasColumn('maintenances', 'odometer')) {
                $table->unsignedBigInteger('odometer')->nullable()->after('cost');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_trainings');

        Schema::table('trucks', function (Blueprint $table) {
            foreach ([
                'last_maintenance_mileage',
                'maintenance_mileage_threshold',
                'maintenance_interval_days',
            ] as $column) {
                if (Schema::hasColumn('trucks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('maintenances', function (Blueprint $table) {
            if (Schema::hasColumn('maintenances', 'odometer')) {
                $table->dropColumn('odometer');
            }
        });
    }
};
