<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotspots', function (Blueprint $table) {
            $table->id();
            $table->string('external_id');
            $table->json('sources'); // ["sipongi","firms"]
            $table->string('satellite')->nullable();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->string('confidence'); // low|medium|high
            $table->string('confidence_raw')->nullable();
            $table->decimal('frp', 8, 2)->nullable();
            $table->timestamp('detected_at');
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('corroborated')->default(false);
            $table->json('raw')->nullable();
            $table->timestamps();

            $table->index(['detected_at', 'district_id']);
            $table->unique(['external_id', 'detected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotspots');
    }
};
