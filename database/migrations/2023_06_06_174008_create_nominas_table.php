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


        Schema::create('nominas', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_final_pago')->nullable();
            $table->date('fecha_inicial_pago')->nullable();
            $table->date('fecha_pago')->nullable();
            $table->float('num_dias_pagados')->nullable();
            $table->string('tipo_nomina')->nullable();
            $table->float('total_deducciones')->nullable();
            $table->float('total_otros_pagos')->nullable();
            $table->float('total_percepciones')->nullable();
            $table->string('version')->nullable();
            $table->foreignId('complemento_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nominas');
    }
};
