<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sunat_document_types', function (Blueprint $table) {
            $table->string('code', 2)->primary();
            $table->string('description');
            $table->string('sunat_name');
            $table->timestamps();
        });

        Schema::create('sunat_tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('description');
            $table->decimal('rate', 5, 2);
            $table->string('type')->default('IGV');
            $table->timestamps();
            $table->unique(['code', 'type']);
        });

        Schema::create('sunat_error_codes', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('category')->nullable();
            $table->string('message');
            $table->text('resolution')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('event');
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('sunat_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('operation');
            $table->text('endpoint')->nullable();
            $table->longText('request_payload')->nullable();
            $table->longText('response_payload')->nullable();
            $table->string('status_code')->nullable();
            $table->boolean('is_success')->default(false);
            $table->timestamp('executed_at')->useCurrent();
            $table->timestamps();
            $table->index(['invoice_id', 'operation']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sunat_logs');
        Schema::dropIfExists('invoice_audits');
        Schema::dropIfExists('sunat_error_codes');
        Schema::dropIfExists('sunat_tax_rates');
        Schema::dropIfExists('sunat_document_types');
    }
};
