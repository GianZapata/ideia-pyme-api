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
        Schema::create('nomina_otro_pago', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->nullable();
            $table->string('concepto')->nullable();
            $table->string('importe')->nullable();
            $table->string('tipo_otro_pago')->nullable();
            $table->foreignId('nomina_id')->nullable()->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nomina_otro_pago');
    }
};
