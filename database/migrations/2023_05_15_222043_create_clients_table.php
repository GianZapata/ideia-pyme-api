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

            $table->string('name'); // Nombre de la Empresa / Persona Física Actividad Empresarial:
            $table->year("anioConstitucion"); // Año de constitución:
            $table->string("sector_actividad"); // Sector actividad:

            /** Cuenta de resultados */
            $table->float("ventas", 12); // Ventas
            $table->float("ventasAnterior", 12); // Ventas Año anterior:
            $table->float("trabActivo", 12); // Trabajos realizados para el activo
            $table->float("otrosIng", 12); // Otros ingresos de explotación
            $table->float("resExplotacion", 12); // Resultado de explotación
            $table->float("resFinanciero", 12); // Resultado financiero
            $table->float("resAntesImp", 12); // Resultado antes de impuestos

            /** Activo */
            $table->float("deudoresComerciales", 12); // Deudores comerciales
            $table->float("inversionesFin", 12); // Inversiones financieras corto plazo
            $table->float("efectivoLiquidez", 12); // Efectivo o liquidez
            $table->float("activoTotal", 12); // Activo Total

            /** Pasivo */
            $table->float("pasivoNoCirculante", 12); // Pasivo no circulante
            $table->float("provisionesLargoPlazo", 12); // Provisiones a largo plazo
            $table->float("pasivoCirculante", 12); // Pasivo circulante
            $table->float("capitalContable", 12); // Capital contable
            $table->float("prestamosActuales", 12); // Prestamos Actuales

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
