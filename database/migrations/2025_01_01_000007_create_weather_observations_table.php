<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained()->cascadeOnDelete();
            $table->date('observed_at');
            $table->decimal('temp_max', 5, 2)->nullable();
            $table->decimal('rh_min', 5, 2)->nullable();
            $table->decimal('wind_max', 5, 2)->nullable();
            $table->decimal('rain_mm', 6, 2)->nullable();
            $table->unsignedSmallInteger('dry_days')->nullable();
            $table->string('source'); // open_meteo|bmkg
            $table->boolean('is_forecast')->default(false);
            $table->timestamps();

            $table->unique(['district_id', 'observed_at', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_observations');
    }
};
