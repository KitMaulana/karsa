<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeatherObservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'district_id', 'observed_at', 'temp_max', 'rh_min', 'wind_max', 'rain_mm',
        'dry_days', 'source', 'is_forecast', 'is_demo',
    ];

    protected $casts = [
        'observed_at' => 'date',
        'temp_max' => 'float',
        'rh_min' => 'float',
        'wind_max' => 'float',
        'rain_mm' => 'float',
        'is_forecast' => 'boolean',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
