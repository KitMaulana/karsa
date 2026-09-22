<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authority_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('agency'); // BPBD|Manggala Agni|Damkar|Polsek|Kepala Desa
            $table->foreignId('regency_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('min_level')->default('tinggi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authority_contacts');
    }
};
