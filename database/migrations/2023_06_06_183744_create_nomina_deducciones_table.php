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
        Schema::create('nomina_deducciones', function (Blueprint $table) {
            $table->id();
            $table->float('total_otras_deducciones')->nullable();
            $table->float('total_impuestos_retenidos')->nullable();
            $table->foreignId('nomina_id')->nullable()->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nomina_deducciones');
    }
};
