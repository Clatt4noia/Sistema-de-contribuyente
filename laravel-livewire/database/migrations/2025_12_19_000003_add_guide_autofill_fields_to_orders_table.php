<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'origin_ubigeo')) {
                $table->string('origin_ubigeo', 6)->nullable()->after('origin');
            }

            if (! Schema::hasColumn('orders', 'origin_address')) {
                $table->string('origin_address', 255)->nullable()->after('origin_ubigeo');
            }

            if (! Schema::hasColumn('orders', 'destination_ubigeo')) {
                $table->string('destination_ubigeo', 6)->nullable()->after('destination');
            }

            if (! Schema::hasColumn('orders', 'destination_address')) {
                $table->string('destination_address', 255)->nullable()->after('destination_ubigeo');
            }

            if (! Schema::hasColumn('orders', 'total_packages')) {
                $table->unsignedInteger('total_packages')->nullable()->after('cargo_volume_m3');
            }

            if (! Schema::hasColumn('orders', 'destinatario_document_type')) {
                $table->string('destinatario_document_type', 2)->nullable()->after('client_id');
            }

            if (! Schema::hasColumn('orders', 'destinatario_document_number')) {
                $table->string('destinatario_document_number', 20)->nullable()->after('destinatario_document_type');
            }

            if (! Schema::hasColumn('orders', 'destinatario_name')) {
                $table->string('destinatario_name', 150)->nullable()->after('destinatario_document_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $drop = [
                'origin_ubigeo',
                'origin_address',
                'destination_ubigeo',
                'destination_address',
                'total_packages',
                'destinatario_document_type',
                'destinatario_document_number',
                'destinatario_name',
            ];

            foreach ($drop as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

