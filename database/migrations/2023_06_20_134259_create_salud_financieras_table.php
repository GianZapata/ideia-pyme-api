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
        Schema::create('salud_financieras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('cascade');

            /** Cuenta de resultados */
            $table->float("ventas", 12)->nullable(); // Ventas
            $table->float("ventasAnterior", 12)->nullable(); // Ventas Año anterior:
            $table->float("trabActivo", 12)->nullable(); // Trabajos realizados para el activo
            $table->float("otrosIng", 12)->nullable(); // Otros ingresos de explotación
            $table->float("resExplotacion", 12)->nullable(); // Resultado de explotación
            $table->float("resFinanciero", 12)->nullable(); // Resultado financiero
            $table->float("resAntesImp", 12)->nullable(); // Resultado antes de impuestos

            /** Activo */
            $table->float("deudoresComerciales", 12)->nullable(); // Deudores comerciales
            $table->float("inversionesFin", 12)->nullable(); // Inversiones financieras corto plazo
            $table->float("efectivoLiquidez", 12)->nullable(); // Efectivo o liquidez
            $table->float("activoTotal", 12)->nullable(); // Activo Total

            /** Pasivo */
            $table->float("pasivoNoCirculante", 12)->nullable(); // Pasivo no circulante
            $table->float("provisionesLargoPlazo", 12)->nullable(); // Provisiones a largo plazo
            $table->float("pasivoCirculante", 12)->nullable(); // Pasivo circulante
            $table->float("capitalContable", 12)->nullable(); // Capital contable
            $table->float("prestamosActuales", 12)->nullable(); // Prestamos Actuales

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

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salud_financieras');
    }
};
