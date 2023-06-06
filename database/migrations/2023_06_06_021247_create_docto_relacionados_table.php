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
        Schema::create('docto_relacionados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_id')->constrained();
            $table->string('id_documento')->nullable();
            $table->string('serie')->nullable();
            $table->string('folio')->nullable();
            $table->string('moneda_dr')->nullable();
            $table->decimal('equivalencia_dr', 10, 4)->nullable();
            $table->integer('num_parcialidad')->nullable();
            $table->decimal('imp_saldo_ant', 10, 2)->nullable();
            $table->decimal('imp_pagado', 10, 2)->nullable();
            $table->decimal('imp_saldo_insoluto', 10, 2)->nullable();
            $table->string('objeto_imp_dr')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('docto_relacionados');
    }
};
