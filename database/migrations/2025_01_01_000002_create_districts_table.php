<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regency_id')->constrained('regencies')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->json('geometry')->nullable();
            $table->decimal('centroid_lat', 10, 7)->nullable();
            $table->decimal('centroid_lng', 10, 7)->nullable();
            $table->decimal('area_ha', 12, 2)->nullable();
            $table->string('bmkg_adm4')->nullable();
            $table->unsignedTinyInteger('vulnerability_score')->default(0);
            $table->json('vulnerability_factors')->nullable();
            $table->boolean('is_monitored')->default(true);
            $table->timestamps();

            $table->index(['regency_id', 'is_monitored']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
