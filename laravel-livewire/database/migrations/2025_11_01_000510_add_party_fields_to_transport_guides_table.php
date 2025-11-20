<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_guides', function (Blueprint $table) {
            $table->string('remitente_document_type', 2)->default('6')->after('client_id');
            $table->string('remitente_document_number', 20)->nullable()->after('remitente_document_type');
            $table->string('destinatario_document_type', 2)->nullable()->after('remitente_name');
            $table->string('destinatario_document_number', 20)->nullable()->after('destinatario_document_type');
            $table->string('destinatario_name', 100)->nullable()->after('destinatario_document_number');
        });
    }

    public function down(): void
    {
        Schema::table('transport_guides', function (Blueprint $table) {
            $table->dropColumn([
                'remitente_document_type',
                'remitente_document_number',
                'destinatario_document_type',
                'destinatario_document_number',
                'destinatario_name',
            ]);
        });
    }
};
