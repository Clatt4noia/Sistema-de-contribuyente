<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('drivers') && Schema::hasColumn('drivers', 'status')) {
            DB::table('drivers')->where('status', 'available')->update(['status' => 'active']);
            DB::table('drivers')->where('status', 'activo')->update(['status' => 'active']);
            DB::table('drivers')->where('status', 'asignado')->update(['status' => 'assigned']);
            DB::table('drivers')->where('status', 'inactivo')->update(['status' => 'inactive']);
            DB::table('drivers')->where('status', 'baja')->update(['status' => 'inactive']);
            DB::table('drivers')->where('status', 'desactivado')->update(['status' => 'inactive']);
            DB::table('drivers')->where('status', 'permiso')->update(['status' => 'on_leave']);
            DB::table('drivers')->where('status', 'de permiso')->update(['status' => 'on_leave']);
            DB::table('drivers')->where('status', 'leave')->update(['status' => 'on_leave']);
        }

        if (Schema::hasTable('trucks') && Schema::hasColumn('trucks', 'status')) {
            DB::table('trucks')->where('status', 'active')->update(['status' => 'available']);
            DB::table('trucks')->where('status', 'in_maintenance')->update(['status' => 'maintenance']);
        }

        if (Schema::hasTable('maintenances') && Schema::hasColumn('maintenances', 'status')) {
            DB::table('maintenances')->where('status', 'pending')->update(['status' => 'scheduled']);
        }

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'status')) {
            DB::table('orders')->where('status', 'canceled')->update(['status' => 'cancelled']);
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'pgsql') {
            return;
        }

        DB::statement("ALTER TABLE drivers DROP CONSTRAINT IF EXISTS drivers_status_check");
        DB::statement("ALTER TABLE drivers ADD CONSTRAINT drivers_status_check CHECK (status IN ('active','assigned','inactive','on_leave'))");

        DB::statement("ALTER TABLE trucks DROP CONSTRAINT IF EXISTS trucks_status_check");
        DB::statement("ALTER TABLE trucks ADD CONSTRAINT trucks_status_check CHECK (status IN ('available','in_use','maintenance','out_of_service','reserved'))");

        DB::statement("ALTER TABLE maintenances DROP CONSTRAINT IF EXISTS maintenances_status_check");
        DB::statement("ALTER TABLE maintenances ADD CONSTRAINT maintenances_status_check CHECK (status IN ('scheduled','in_progress','completed','cancelled'))");

        DB::statement("ALTER TABLE assignments DROP CONSTRAINT IF EXISTS assignments_status_check");
        DB::statement("ALTER TABLE assignments ADD CONSTRAINT assignments_status_check CHECK (status IN ('scheduled','in_progress','completed','cancelled'))");
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'pgsql') {
            return;
        }

        DB::statement("ALTER TABLE drivers DROP CONSTRAINT IF EXISTS drivers_status_check");
        DB::statement("ALTER TABLE trucks DROP CONSTRAINT IF EXISTS trucks_status_check");
        DB::statement("ALTER TABLE maintenances DROP CONSTRAINT IF EXISTS maintenances_status_check");
        DB::statement("ALTER TABLE assignments DROP CONSTRAINT IF EXISTS assignments_status_check");
    }
};

