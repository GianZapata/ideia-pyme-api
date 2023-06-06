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
        Schema::create('impuestos', function (Blueprint $table) {
            $table->id();
            $table->string('impuestoable_type');
            $table->integer('impuestoable_id');
            $table->string('impuesto')->nullable();
            $table->string('base')->nullable();
            $table->string('tipo_factor')->nullable();
            $table->string('tasa_o_cuota')->nullable();
            $table->string('importe')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('impuestos');
    }
};
