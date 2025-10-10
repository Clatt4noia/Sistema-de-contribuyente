<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'cargo_type_id')) {
                $table->foreignId('cargo_type_id')->nullable()->after('client_id')->constrained()->nullOnDelete();
            }

            if (!Schema::hasColumn('orders', 'cargo_weight_kg')) {
                $table->decimal('cargo_weight_kg', 10, 2)->nullable()->after('cargo_details');
            }

            if (!Schema::hasColumn('orders', 'cargo_volume_m3')) {
                $table->decimal('cargo_volume_m3', 10, 2)->nullable()->after('cargo_weight_kg');
            }

            if (!Schema::hasColumn('orders', 'delivery_window_start')) {
                $table->dateTime('delivery_window_start')->nullable()->after('delivery_date');
            }

            if (!Schema::hasColumn('orders', 'delivery_window_end')) {
                $table->dateTime('delivery_window_end')->nullable()->after('delivery_window_start');
            }

            if (!Schema::hasColumn('orders', 'origin_latitude')) {
                $table->decimal('origin_latitude', 10, 7)->nullable()->after('origin');
                $table->decimal('origin_longitude', 10, 7)->nullable()->after('origin_latitude');
            }

            if (!Schema::hasColumn('orders', 'destination_latitude')) {
                $table->decimal('destination_latitude', 10, 7)->nullable()->after('destination');
                $table->decimal('destination_longitude', 10, 7)->nullable()->after('destination_latitude');
            }

            if (!Schema::hasColumn('orders', 'estimated_cost')) {
                $table->decimal('estimated_cost', 12, 2)->nullable()->after('estimated_duration_hours');
            }

            if (!Schema::hasColumn('orders', 'cost_breakdown')) {
                $table->json('cost_breakdown')->nullable()->after('estimated_cost');
            }
        });

        Schema::create('cargo_type_truck', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cargo_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('truck_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['cargo_type_id', 'truck_id']);
        });

        Schema::create('route_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reported_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type');
            $table->enum('severity', ['low', 'medium', 'high'])->default('low');
            $table->text('description')->nullable();
            $table->enum('status', ['open', 'in_progress', 'resolved'])->default('open');
            $table->timestamp('reported_at');
            $table->timestamp('resolved_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('vehicle_location_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('truck_id')->constrained()->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('speed_kph', 6, 2)->nullable();
            $table->timestamp('reported_at');
            $table->enum('status', ['on_route', 'delayed', 'off_route'])->default('on_route');
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['truck_id', 'reported_at']);
        });

        Schema::create('inventory_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('item_sku');
            $table->decimal('quantity', 12, 3);
            $table->enum('status', ['pending', 'confirmed', 'released', 'failed'])->default('pending');
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->string('source_system')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'item_sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_reservations');
        Schema::dropIfExists('vehicle_location_updates');
        Schema::dropIfExists('route_incidents');
        Schema::dropIfExists('cargo_type_truck');
        Schema::dropIfExists('cargo_types');

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'cost_breakdown')) {
                $table->dropColumn('cost_breakdown');
            }

            if (Schema::hasColumn('orders', 'estimated_cost')) {
                $table->dropColumn('estimated_cost');
            }

            if (Schema::hasColumn('orders', 'destination_longitude')) {
                $table->dropColumn(['destination_longitude', 'destination_latitude']);
            }

            if (Schema::hasColumn('orders', 'origin_longitude')) {
                $table->dropColumn(['origin_longitude', 'origin_latitude']);
            }

            if (Schema::hasColumn('orders', 'delivery_window_end')) {
                $table->dropColumn(['delivery_window_end', 'delivery_window_start']);
            }

            if (Schema::hasColumn('orders', 'cargo_volume_m3')) {
                $table->dropColumn(['cargo_volume_m3', 'cargo_weight_kg']);
            }

            if (Schema::hasColumn('orders', 'cargo_type_id')) {
                $table->dropConstrainedForeignId('cargo_type_id');
            }
        });
    }
};
