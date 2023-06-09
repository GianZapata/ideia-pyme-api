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
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->nullable()->constrained('emisores')->onDelete('cascade');
            $table->foreignId('receptor_id')->nullable()->constrained('receptores')->onDelete('cascade');
            $table->uuid('uuid')->unique();
            $table->string('tipo'); // emitido o recibido
            $table->boolean('cancelado')->default(false);
            $table->date('fecha');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
