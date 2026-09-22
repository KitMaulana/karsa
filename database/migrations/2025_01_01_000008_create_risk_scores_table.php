<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained()->cascadeOnDelete();
            $table->foreignId('risk_model_id')->constrained()->cascadeOnDelete();
            $table->timestamp('calculated_at');
            $table->decimal('score', 5, 2);
            $table->string('level'); // rendah|sedang|tinggi|sangat_tinggi
            $table->decimal('s_hotspot', 5, 2);
            $table->decimal('s_weather', 5, 2);
            $table->decimal('s_vulnerability', 5, 2);
            $table->decimal('data_confidence', 5, 2)->nullable();
            $table->decimal('co2_estimate_t', 10, 2)->nullable();
            $table->json('detail')->nullable(); // proyeksi 72 jam, sub-skor cuaca, dsb
            $table->timestamps();

            $table->index(['district_id', 'calculated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_scores');
    }
};
