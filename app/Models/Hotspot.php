<?php

namespace App\Models;

use App\Enums\HotspotConfidence;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hotspot extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id', 'sources', 'satellite', 'lat', 'lng', 'confidence', 'confidence_raw',
        'frp', 'detected_at', 'district_id', 'corroborated', 'raw',
    ];

    protected $casts = [
        'sources' => 'array',
        'raw' => 'array',
        'lat' => 'float',
        'lng' => 'float',
        'frp' => 'float',
        'detected_at' => 'datetime',
        'corroborated' => 'boolean',
        'confidence' => HotspotConfidence::class,
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
