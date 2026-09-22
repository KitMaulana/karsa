<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->decimal('accuracy_m', 8, 2)->nullable();
            $table->string('type'); // asap|api_kecil|api_besar|pembakaran_lahan
            $table->text('description');
            $table->json('media')->nullable(); // path privat & publik, phash
            $table->json('exif')->nullable();
            $table->string('phash')->nullable();
            $table->decimal('trust_score', 5, 2)->default(0);
            $table->json('flags')->nullable();
            $table->string('status')->default('baru');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('forwarded_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['district_id', 'status']);
            $table->index('phash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
