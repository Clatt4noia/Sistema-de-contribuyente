<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubigeos', function (Blueprint $table) {
            $table->string('code', 6)->primary(); // 150101
            $table->string('department');
            $table->string('province');
            $table->string('district');
            $table->string('reniec_code', 6)->nullable();
            $table->timestamps();
            
            $table->index('department');
            $table->index('province');
            $table->index('district');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubigeos');
    }
};
