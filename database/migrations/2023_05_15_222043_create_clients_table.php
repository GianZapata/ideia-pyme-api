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
            $table->float("ventas"); // Ventas
            $table->float("ventasAnterior"); // Ventas Año anterior:
            $table->float("trabActivo"); // Trabajos realizados para el activo
            $table->float("otrosIng"); // Otros ingresos de explotación
            $table->float("resExplotacion"); // Resultado de explotación
            $table->float("resFinanciero"); // Resultado financiero
            $table->float("resAntesImp"); // Resultado antes de impuestos

            /** Activo */
            $table->float("deudoresComerciales"); // Deudores comerciales
            $table->float("inversionesFin"); // Inversiones financieras corto plazo
            $table->float("efectivoLiquidez"); // Efectivo o liquidez
            $table->float("activoTotal"); // Activo Total

            /** Pasivo */
            $table->float("pasivoNoCirculante"); // Pasivo no circulante
            $table->float("provisionesLargoPlazo"); // Provisiones a largo plazo
            $table->float("pasivoCirculante"); // Pasivo circulante
            $table->float("capitalContable"); // Capital contable
            $table->float("prestamosActuales"); // Prestamos Actuales

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
