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
        Schema::create('subsidio_empleos', function (Blueprint $table) {
            $table->id();
            $table->float('subsidio_causado')->nullable();
            $table->foreignId('nomina_otro_pago_id')->nullable()->constrained('nomina_otro_pago');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subsidio_empleos');
    }
};
