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
        Schema::create('nomina_deduccion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nomina_deducciones_id')->nullable();
            $table->foreign('nomina_deducciones_id')->references('id')->on('nomina_deducciones')->onDelete('cascade');
            $table->string('clave')->nullable();
            $table->string('concepto')->nullable();
            $table->float('importe')->nullable();
            $table->string('tipo_deduccion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nomina_deduccion');
    }
};
