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
        Schema::create('nomina_receptores', function (Blueprint $table) {
            $table->id();
            $table->string('antiguedad')->nullable();
            $table->string('clave_ent_fed')->nullable();
            $table->string('curp')->nullable();
            $table->string('departamento')->nullable();
            $table->date('fecha_inicio_rel_laboral')->nullable();
            $table->string('num_empleado')->nullable();
            $table->string('num_seguridad_social')->nullable();
            $table->string('periodicidad_pago')->nullable();
            $table->string('puesto')->nullable();
            $table->string('riesgo_puesto')->nullable();
            $table->float('salario_base_cot_apor')->nullable();
            $table->float('salario_diario_integrado')->nullable();
            $table->string('sindicalizado')->nullable();
            $table->string('tipo_contrato')->nullable();
            $table->string('tipo_jornada')->nullable();
            $table->string('tipo_regimen')->nullable();
            $table->foreignId('nomina_id')->constrained()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nomina_receptores');
    }
};
