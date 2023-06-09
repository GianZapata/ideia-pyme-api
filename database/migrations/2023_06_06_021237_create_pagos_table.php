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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complemento_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('version')->nullable();
            $table->decimal('monto_total_pagos', 10, 2)->nullable();
            $table->dateTime('fecha_pago')->nullable();
            $table->string('forma_de_pago_p')->nullable();
            $table->string('moneda_p')->nullable();
            $table->decimal('tipo_cambio_p', 10, 4)->nullable();
            $table->decimal('monto', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
