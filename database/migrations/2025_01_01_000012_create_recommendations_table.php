<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->string('level'); // rendah|sedang|tinggi|sangat_tinggi
            $table->string('audience'); // warga_umum|petani_pekebun|sekolah
            $table->string('icon')->default('leaf');
            $table->string('title');
            $table->text('body');
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();

            $table->index(['level', 'audience', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
