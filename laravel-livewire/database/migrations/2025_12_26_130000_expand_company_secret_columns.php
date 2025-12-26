<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('companies')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE companies ALTER COLUMN sol_pass TYPE TEXT');
            DB::statement('ALTER TABLE companies ALTER COLUMN client_secret TYPE TEXT');
            DB::statement('ALTER TABLE companies ALTER COLUMN cert_path TYPE TEXT');
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE companies MODIFY sol_pass TEXT');
            DB::statement('ALTER TABLE companies MODIFY client_secret TEXT');
            DB::statement('ALTER TABLE companies MODIFY cert_path TEXT');
        }
    }

    public function down(): void
    {
        // No down migration: shrinking columns can truncate data.
    }
};

