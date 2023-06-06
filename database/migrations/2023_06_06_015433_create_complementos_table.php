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
        Schema::create('complementos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained()->onDelete('cascade');
            $table->string('version')->nullable();
            $table->string('rfc_prov_certif')->nullable();
            $table->string('uuid')->nullable();
            $table->dateTime('fecha_timbrado')->nullable();
            $table->string('sello_cfd')->nullable();
            $table->string('no_certificado_sat')->nullable();
            $table->string('sello_sat')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complementos');
    }
};
