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
        Schema::create('comprobantes', function (Blueprint $table) {
            $table->id();
            $table->text('certificado')->nullable();
            $table->dateTime('fecha')->nullable();
            $table->string('lugar_expedicion')->nullable();
            $table->string('moneda')->nullable();
            $table->text('no_certificado')->nullable();
            $table->text('sello')->nullable();
            $table->decimal('sub_total', 10, 2)->nullable();
            $table->string('tipo_comprobante')->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->float('version')->nullable();
            $table->integer('folio')->nullable();
            $table->integer('forma_pago')->nullable();
            $table->string('metodo_pago')->nullable();
            $table->string('serie')->nullable();
            $table->decimal('tipo_cambio', 10, 2)->nullable();
            $table->string('exportacion')->nullable();
            $table->string('condiciones_de_pago')->nullable();
            $table->foreignId('factura_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comprobantes');
    }
};
