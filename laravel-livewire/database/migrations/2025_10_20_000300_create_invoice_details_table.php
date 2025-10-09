<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('invoice_details')) {
            Schema::create('invoice_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
                if (Schema::hasTable('orders')) {
                    $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
                } else {
                    $table->unsignedBigInteger('order_id')->nullable();
                }

                if (Schema::hasTable('products')) {
                    $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
                } else {
                    $table->unsignedBigInteger('product_id')->nullable();
                }
                $table->string('description');
                $table->decimal('quantity', 12, 2)->default(1);
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->decimal('tax_percentage', 5, 2)->default(18);
                $table->decimal('tax_amount', 12, 2)->default(0);
                $table->decimal('taxable_amount', 12, 2)->default(0);
                $table->decimal('total', 12, 2)->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });

            return;
        }

        Schema::table('invoice_details', function (Blueprint $table) {
            if (! Schema::hasColumn('invoice_details', 'order_id')) {
                if (Schema::hasTable('orders')) {
                    $table->foreignId('order_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
                } else {
                    $table->unsignedBigInteger('order_id')->nullable()->after('product_id');
                }
            }

            if (! Schema::hasColumn('invoice_details', 'metadata')) {
                $table->json('metadata')->nullable()->after('total');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_details');
    }
};
