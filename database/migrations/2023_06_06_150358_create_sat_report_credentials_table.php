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
        Schema::create('sat_report_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sat_report_id')->constrained()->cascadeOnDelete();
            $table->string('credentials_type')->nullable()->default('ciec');
            $table->string('rfc');
            $table->string('password');
            $table->string('cer_attachment_id')->nullable();
            $table->string('key_attachment_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sat_report_credentials');
    }
};
