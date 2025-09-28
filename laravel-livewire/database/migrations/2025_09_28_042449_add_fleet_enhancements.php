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
            if (!Schema::hasColumn('trucks', 'mileage')) {
                $table->unsignedBigInteger('mileage')->default(0)->after('capacity');
            }
        });

        Schema::table('drivers', function (Blueprint $table) {
            if (!Schema::hasColumn('drivers', 'work_schedule')) {
                $table->json('work_schedule')->nullable()->after('status');
            }
        });

        Schema::create('driver_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->string('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });

        Schema::create('driver_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->string('evaluator')->nullable();
            $table->text('comments')->nullable();
            $table->timestamp('evaluated_at');
            $table->timestamps();
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('business_name');
            $table->string('tax_id')->unique();
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('payment_terms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('origin');
            $table->string('destination');
            $table->dateTime('pickup_date')->nullable();
            $table->dateTime('delivery_date')->nullable();
            $table->enum('status', ['pending', 'en_route', 'delivered', 'cancelled'])->default('pending');
            $table->text('cargo_details')->nullable();
            $table->decimal('estimated_distance_km', 8, 2)->nullable();
            $table->decimal('estimated_duration_hours', 8, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('route_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('planner')->nullable();
            $table->text('route_summary')->nullable();
            $table->string('map_url')->nullable();
            $table->json('route_data')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->enum('status', ['draft', 'issued', 'paid', 'overdue'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('paid_at');
            $table->string('method')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('assignments', 'order_id')) {
                if (Schema::getConnection()->getDriverName() === 'sqlite') {
                    $table->unsignedBigInteger('order_id')->nullable()->after('driver_id');
                } else {
                    $table->foreignId('order_id')->nullable()->after('driver_id')->constrained()->cascadeOnDelete();
                }
            }

            if (!Schema::hasColumn('assignments', 'notes')) {
                $table->text('notes')->nullable()->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            if (Schema::hasColumn('assignments', 'order_id')) {
                if (Schema::getConnection()->getDriverName() === 'sqlite') {
                    $table->dropColumn('order_id');
                } else {
                    $table->dropConstrainedForeignId('order_id');
                }
            }

            if (Schema::hasColumn('assignments', 'notes')) {
                $table->dropColumn('notes');
            }
        });

        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('route_plans');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('driver_evaluations');
        Schema::dropIfExists('driver_schedules');

        Schema::table('drivers', function (Blueprint $table) {
            if (Schema::hasColumn('drivers', 'work_schedule')) {
                $table->dropColumn('work_schedule');
            }
        });

        Schema::table('trucks', function (Blueprint $table) {
            if (Schema::hasColumn('trucks', 'mileage')) {
                $table->dropColumn('mileage');
            }
        });
    }
};
