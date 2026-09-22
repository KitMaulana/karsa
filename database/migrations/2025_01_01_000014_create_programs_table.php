<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('cover')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->string('location')->nullable();
            $table->unsignedInteger('volunteer_quota')->nullable();
            $table->boolean('donation_enabled')->default(false);
            $table->decimal('donation_target', 12, 2)->nullable();
            $table->decimal('donation_collected', 12, 2)->default(0);
            $table->json('payment_info')->nullable(); // rekening/QRIS
            $table->longText('usage_report')->nullable();
            $table->string('status')->default('draf'); // draf|terbit|selesai
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
