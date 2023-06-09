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
        Schema::create('comprobante_traslados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comprobante_id')->constrained()->onDelete('cascade');
            $table->decimal('base', 10, 2)->nullable();
            $table->string('impuesto')->nullable();
            $table->string('tipo_factor')->nullable();
            $table->decimal('tasa_o_cuota', 10, 6)->nullable();
            $table->decimal('importe', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comprobante_traslados');
    }
};
