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
        Schema::create('nomina_percepcion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nomina_percepciones_id')->nullable();
            $table->foreign('nomina_percepciones_id')->references('id')->on('nomina_percepciones')->onDelete('cascade');
            $table->string('clave')->nullable();
            $table->string('concepto')->nullable();
            $table->float('importe_exento')->nullable();
            $table->float('importe_gravado')->nullable();
            $table->string('tipo_percepcion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nomina_percepcion');
    }
};
