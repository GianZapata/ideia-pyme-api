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
            $table->year("anioConstitucion")->nullable(); // Año de constitución:
            $table->string("sector_actividad")->nullable(); // Sector actividad:

            $table->string('street')->nullable();
            $table->string('house_number')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('municipality')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();

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
