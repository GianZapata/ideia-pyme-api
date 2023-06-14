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
        Schema::create('sat_report_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sat_report_id')->constrained()->cascadeOnDelete();
            $table->string('rfc');
            $table->string('curp');
            $table->string('name');
            $table->string('last_name');
            $table->string('second_last_name');
            $table->float('percentage');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sat_report_partners');
    }
};
