<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_guide_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\TransportGuide::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->string('description', 250);
            $table->string('unit_of_measure', 4);
            $table->decimal('quantity', 12, 3);
            $table->decimal('weight', 12, 3)->nullable();
            $table->unsignedBigInteger('order_item_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_guide_items');
    }
};
