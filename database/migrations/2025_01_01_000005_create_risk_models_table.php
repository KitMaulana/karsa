<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('matrix'); // matriks utama 3x3 (hotspot, cuaca, kerentanan)
            $table->json('sub_matrix'); // matriks sub-faktor cuaca 4x4
            $table->json('weights'); // {hotspot, cuaca, kerentanan}
            $table->json('sub_weights'); // {temp_max, rh_min, wind_max, dry_days}
            $table->decimal('cr', 6, 4)->default(0);
            $table->decimal('sub_cr', 6, 4)->default(0);
            $table->json('thresholds'); // {rendah:[0,39], sedang:[40,64], ...}
            $table->json('params')->nullable(); // hotspot_max, buffer_km, dsb (override config)
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_models');
    }
};
