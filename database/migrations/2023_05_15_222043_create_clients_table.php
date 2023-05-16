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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');

            $table->float('score', 8, 2)->nullable()->check('score >= 300 and score <= 850');
            $table->string('rfc')->nullable();

            $table->string('name')->nullable(); // Nombre de la Empresa / Persona Fisica Actividad Empresarial:
            $table->year("anioConstitucion")->nullable(); // Año de constitución:
            $table->string("sector_actividad")->nullable(); // Sector actividad:

            /** Cuenta de resultados */
            $table->float("ventas")->nullable(); // Ventas
            $table->float("ventasAnterior")->nullable(); // Ventas Año anterior:
            $table->float("trabActivo")->nullable(); // Trabajos realizados para el activo
            $table->float("otrosIng")->nullable(); // Otros ingresos de explotación
            $table->float("resExplotacion")->nullable(); // Resultado de explotación
            $table->float("resFinanciero")->nullable(); // Resultado financiero
            $table->float("resAntesImp")->nullable(); // Resultado antes de impuestos

            /** Activo */
            $table->float("deudoresComerciales")->nullable(); // Deudores comerciales
            $table->float("inversionesFin")->nullable(); // Inversiones financieras corto plazo
            $table->float("efectivoLiquidez")->nullable(); // Efectivo o liquidez
            $table->float("activoTotal")->nullable(); // Activo Total

            /** Pasivo */
            $table->float("pasivoNoCirculante")->nullable(); // Pasivo no circulante
            $table->float("provisionesLargoPlazo")->nullable(); // Provisiones a largo plazo
            $table->float("pasivoCirculante")->nullable(); // Pasivo circulante
            $table->float("capitalContable")->nullable(); // Capital contable
            $table->float("prestamosActuales")->nullable(); // Prestamos Actuales

            /** Cuantitativo */
            $table->integer("antiguedadEmpresa")->nullable();
            $table->integer("reconocimientoMercado")->nullable();
            $table->integer("informeComercial")->nullable();
            $table->integer("infraestructura")->nullable();
            $table->integer("problemasLegales")->nullable();
            $table->integer("calidadCartera")->nullable();
            $table->integer("referenciasBancarias")->nullable();
            $table->integer("referenciasComerciales")->nullable();
            $table->integer("importanciaMop")->nullable();
            $table->integer("perteneceHolding")->nullable();
            $table->integer("idAnalisis")->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
