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
        Schema::create('client_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('street')->nullable();
            $table->string('house_number')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('municipality')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->float('score', 8, 2)->nullable()->check('score >= 300 and score <= 850');
            $table->date('birth_date')->nullable();
            $table->string('rfc')->nullable();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            /** Data */
            $table->year("anioConstitucion")->nullable();
            $table->string("sector_actividad")->nullable();
            $table->float("ventas")->nullable();
            $table->float("ventasAnterior")->nullable();
            $table->boolean("trabActivo")->nullable()->default(false);
            $table->float("otrosIng")->nullable();
            $table->float("resExplotacion")->nullable();
            $table->float("resFinanciero")->nullable();
            $table->float("resAntesImp")->nullable();
            $table->float("deudoresComerciales")->nullable();
            $table->float("inversionesFin")->nullable();
            $table->float("efectivoLiquidez")->nullable();
            $table->float("activoTotal")->nullable();
            $table->float("pasivoNoCirculante")->nullable();
            $table->float("provisionesLargoPlazo")->nullable();
            $table->float("pasivoCirculante")->nullable();
            $table->float("capitalContable")->nullable();
            $table->float("prestamosActuales")->nullable();

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
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_profiles');
    }
};
