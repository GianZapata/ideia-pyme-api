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
        Schema::create('conceptos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comprobante_id')->constrained()->onDelete('cascade');
            $table->string('clave_unidad')->nullable();
            $table->string('clave_prod_serv')->nullable();
            $table->string('no_identificacion')->nullable();
            $table->integer('cantidad')->nullable();
            $table->string('unidad')->nullable();
            $table->string('descripcion')->nullable();
            $table->decimal('valor_unitario', 10, 2)->nullable();
            $table->decimal('importe', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conceptos');
    }
};
