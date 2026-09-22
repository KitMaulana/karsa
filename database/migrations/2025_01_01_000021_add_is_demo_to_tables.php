<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['hotspots', 'weather_observations', 'reports', 'alerts', 'risk_scores'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->boolean('is_demo')->default(false)->index();
            });
        }
    }

    public function down(): void
    {
        foreach (['hotspots', 'weather_observations', 'reports', 'alerts', 'risk_scores'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('is_demo');
            });
        }
    }
};
