<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('assignments') || ! Schema::hasTable('orders')) {
            return;
        }

        Schema::table('assignments', function (Blueprint $table) {
            $table->index(['truck_id', 'driver_id'], 'assignments_truck_driver_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['status', 'pickup_date'], 'orders_status_pickup_date_idx');
        });

        $driver = Schema::getConnection()->getDriverName();

        if (! in_array($driver, ['pgsql', 'sqlite'], true)) {
            return;
        }

        if (! Schema::hasTable('documents')) {
            return;
        }

        DB::statement('CREATE INDEX IF NOT EXISTS documents_documentable_type_documentable_id_index ON documents (documentable_type, documentable_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS documents_expires_at_index ON documents (expires_at)');
    }

    public function down(): void
    {
        if (Schema::hasTable('assignments')) {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropIndex('assignments_truck_driver_idx');
        });
        }

        if (Schema::hasTable('orders')) {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_pickup_date_idx');
        });
        }

        $driver = Schema::getConnection()->getDriverName();

        if (! in_array($driver, ['pgsql', 'sqlite'], true)) {
            return;
        }

        if (! Schema::hasTable('documents')) {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS documents_documentable_type_documentable_id_index');
        DB::statement('DROP INDEX IF EXISTS documents_expires_at_index');
    }
};
