<?php

namespace App\Models;

use App\Enums\RiskLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'district_id', 'risk_model_id', 'calculated_at', 'score', 'level',
        's_hotspot', 's_weather', 's_vulnerability', 'data_confidence', 'co2_estimate_t', 'detail',
    ];

    protected $casts = [
        'calculated_at' => 'datetime',
        'score' => 'float',
        's_hotspot' => 'float',
        's_weather' => 'float',
        's_vulnerability' => 'float',
        'data_confidence' => 'float',
        'co2_estimate_t' => 'float',
        'detail' => 'array',
        'level' => RiskLevel::class,
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function riskModel(): BelongsTo
    {
        return $this->belongsTo(RiskModel::class);
    }
}
