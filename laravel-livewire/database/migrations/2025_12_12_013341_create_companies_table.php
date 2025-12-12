<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('ruc', 11)->unique();
            $table->string('razon_social');
            $table->string('nombre_comercial')->nullable();
            $table->string('address')->nullable();
            $table->string('ubigeo', 6)->nullable();
            $table->string('sol_user');
            $table->string('sol_pass');
            $table->string('cert_path')->nullable(); // PFX file path
            $table->string('client_id')->nullable(); // For API
            $table->string('client_secret')->nullable(); // For API
            $table->boolean('production')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
