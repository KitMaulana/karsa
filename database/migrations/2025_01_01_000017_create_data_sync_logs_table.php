<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source'); // sipongi|firms|open_meteo|bmkg
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('finished_at')->nullable();
            $table->string('status'); // sukses|sebagian|gagal
            $table->unsignedInteger('records')->default(0);
            $table->text('message')->nullable();
            $table->timestamps();

            $table->index(['source', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_sync_logs');
    }
};
