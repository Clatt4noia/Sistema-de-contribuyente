<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'document_type')) {
                $table->string('document_type', 2)->nullable()->after('client_id');
            }

            if (!Schema::hasColumn('invoices', 'series')) {
                $table->string('series', 4)->nullable()->after('document_type');
            }

            if (!Schema::hasColumn('invoices', 'correlative')) {
                $table->string('correlative', 8)->nullable()->after('series');
            }

            if (!Schema::hasColumn('invoices', 'ruc_emisor')) {
                $table->string('ruc_emisor', 11)->nullable()->after('correlative');
            }

            if (!Schema::hasColumn('invoices', 'ruc_receptor')) {
                $table->string('ruc_receptor', 11)->nullable()->after('ruc_emisor');
            }

            if (!Schema::hasColumn('invoices', 'currency')) {
                $table->string('currency', 3)->default('PEN')->after('ruc_receptor');
            }

            if (!Schema::hasColumn('invoices', 'taxable_amount')) {
                $table->decimal('taxable_amount', 13, 2)->default(0)->after('subtotal');
            }

            if (!Schema::hasColumn('invoices', 'unaffected_amount')) {
                $table->decimal('unaffected_amount', 13, 2)->default(0)->after('taxable_amount');
            }

            if (!Schema::hasColumn('invoices', 'exempt_amount')) {
                $table->decimal('exempt_amount', 13, 2)->default(0)->after('unaffected_amount');
            }

            if (!Schema::hasColumn('invoices', 'hash')) {
                $table->string('hash')->nullable()->after('total');
            }

            if (!Schema::hasColumn('invoices', 'xml_path')) {
                $table->string('xml_path')->nullable()->after('hash');
            }

            if (!Schema::hasColumn('invoices', 'cdr_path')) {
                $table->string('cdr_path')->nullable()->after('xml_path');
            }

            if (!Schema::hasColumn('invoices', 'metadata')) {
                $table->json('metadata')->nullable()->after('cdr_path');
            }

            if (!Schema::hasColumn('invoices', 'sunat_status')) {
                $table->enum('sunat_status', ['pendiente', 'aceptado', 'rechazado', 'observado'])->default('pendiente')->after('metadata');
            }

            if (!Schema::hasColumn('invoices', 'sunat_sent_at')) {
                $table->timestamp('sunat_sent_at')->nullable()->after('sunat_status');
            }

            if (!Schema::hasColumn('invoices', 'sunat_response_message')) {
                $table->text('sunat_response_message')->nullable()->after('sunat_sent_at');
            }

            if (!Schema::hasColumn('invoices', 'sunat_ticket')) {
                $table->string('sunat_ticket')->nullable()->after('sunat_response_message');
            }

            $table->index(['series', 'correlative'], 'invoices_series_correlative_index');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'sunat_ticket')) {
                $table->dropColumn('sunat_ticket');
            }

            if (Schema::hasColumn('invoices', 'sunat_response_message')) {
                $table->dropColumn('sunat_response_message');
            }

            if (Schema::hasColumn('invoices', 'sunat_sent_at')) {
                $table->dropColumn('sunat_sent_at');
            }

            if (Schema::hasColumn('invoices', 'sunat_status')) {
                $table->dropColumn('sunat_status');
            }

            if (Schema::hasColumn('invoices', 'cdr_path')) {
                $table->dropColumn('cdr_path');
            }

            if (Schema::hasColumn('invoices', 'metadata')) {
                $table->dropColumn('metadata');
            }

            if (Schema::hasColumn('invoices', 'xml_path')) {
                $table->dropColumn('xml_path');
            }

            if (Schema::hasColumn('invoices', 'hash')) {
                $table->dropColumn('hash');
            }

            if (Schema::hasColumn('invoices', 'exempt_amount')) {
                $table->dropColumn('exempt_amount');
            }

            if (Schema::hasColumn('invoices', 'unaffected_amount')) {
                $table->dropColumn('unaffected_amount');
            }

            if (Schema::hasColumn('invoices', 'taxable_amount')) {
                $table->dropColumn('taxable_amount');
            }

            if (Schema::hasColumn('invoices', 'currency')) {
                $table->dropColumn('currency');
            }

            if (Schema::hasColumn('invoices', 'ruc_receptor')) {
                $table->dropColumn('ruc_receptor');
            }

            if (Schema::hasColumn('invoices', 'ruc_emisor')) {
                $table->dropColumn('ruc_emisor');
            }

            if (Schema::hasColumn('invoices', 'series') && Schema::hasColumn('invoices', 'correlative')) {
                $table->dropIndex('invoices_series_correlative_index');
            }

            if (Schema::hasColumn('invoices', 'correlative')) {
                $table->dropColumn('correlative');
            }

            if (Schema::hasColumn('invoices', 'series')) {
                $table->dropColumn('series');
            }

            if (Schema::hasColumn('invoices', 'document_type')) {
                $table->dropColumn('document_type');
            }
        });
    }
};
