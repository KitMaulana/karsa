<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    use HasFactory;

    protected $fillable = [
        'regency_id', 'code', 'name', 'slug', 'geometry', 'centroid_lat', 'centroid_lng',
        'area_ha', 'bmkg_adm4', 'vulnerability_score', 'vulnerability_factors', 'is_monitored',
    ];

    protected $casts = [
        'geometry' => 'array',
        'vulnerability_factors' => 'array',
        'centroid_lat' => 'float',
        'centroid_lng' => 'float',
        'area_ha' => 'float',
        'is_monitored' => 'boolean',
    ];

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    public function hotspots(): HasMany
    {
        return $this->hasMany(Hotspot::class);
    }

    public function weatherObservations(): HasMany
    {
        return $this->hasMany(WeatherObservation::class);
    }

    public function riskScores(): HasMany
    {
        return $this->hasMany(RiskScore::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function watchers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_watch_districts');
    }

    public function latestRiskScore(): HasMany
    {
        return $this->riskScores()->latest('calculated_at');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
