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
        Schema::create('sat_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();

            $table->string('name')->nullable();
            $table->string('street')->nullable();
            $table->string('house_number')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('municipality')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();

            $table->integer('total_tasks')->nullable();
            $table->integer('total_tasks_completed')->nullable();
            $table->boolean('with_partners')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sat_reports');
    }
};
