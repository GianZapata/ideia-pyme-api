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
        Schema::create('nomina_percepciones', function (Blueprint $table) {
            $table->id();
            $table->float('total_exento')->nullable();
            $table->float('total_gravado')->nullable();
            $table->float('total_sueldos')->nullable();
            $table->foreignId('nomina_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nomina_percepciones');
    }
};
