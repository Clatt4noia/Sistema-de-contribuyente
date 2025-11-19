<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_guides', function (Blueprint $table) {
            $table->id();
            $table->string('series', 4);
            $table->unsignedBigInteger('correlative');
            $table->string('full_code', 13);
            $table->date('issue_date');
            $table->time('issue_time');
            $table->string('document_type_code', 2);
            $table->text('observations')->nullable();

            $table->foreignIdFor(\App\Models\Client::class)->constrained();
            $table->string('remitente_ruc', 11);
            $table->string('remitente_name', 100);
            $table->string('transportista_ruc', 11);
            $table->string('transportista_name', 100);

            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('assignment_id')->nullable()->constrained('assignments')->nullOnDelete();
            $table->foreignIdFor(\App\Models\Truck::class)->constrained();
            $table->foreignIdFor(\App\Models\Driver::class)->constrained();
            $table->string('vehicle_plate', 20);
            $table->string('vehicle_brand', 50)->nullable();
            $table->string('mtc_registration_number', 50)->nullable();
            $table->string('driver_document_number', 20);
            $table->string('driver_document_type', 4);
            $table->string('driver_name', 100);
            $table->string('driver_license_number', 20);

            $table->string('transfer_reason_code', 2);
            $table->string('transfer_reason_description', 100)->nullable();
            $table->string('transport_mode_code', 2);
            $table->boolean('scheduled_transshipment');
            $table->date('start_transport_date');
            $table->date('delivery_date')->nullable();
            $table->decimal('gross_weight', 12, 3);
            $table->string('gross_weight_unit', 4)->default('KGM');
            $table->unsignedInteger('total_packages')->nullable();

            $table->string('origin_ubigeo', 8);
            $table->string('origin_address', 100);
            $table->string('destination_ubigeo', 8);
            $table->string('destination_address', 100);

            $table->foreignId('related_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->string('related_invoice_number', 20)->nullable();
            $table->string('related_sender_guide_number', 20)->nullable();
            $table->string('additional_document_reference', 20)->nullable();

            $table->string('sunat_status', 20)->default('draft');
            $table->string('sunat_ticket', 50)->nullable();
            $table->text('sunat_notes')->nullable();
            $table->string('xml_path', 255)->nullable();
            $table->string('cdr_path', 255)->nullable();
            $table->string('pdf_path', 255)->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['series', 'correlative']);
            $table->unique('full_code');
            $table->index(['client_id', 'sunat_status']);
            $table->index(['truck_id', 'driver_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_guides');
    }
};
