<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RiskModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'matrix', 'sub_matrix', 'weights', 'sub_weights', 'cr', 'sub_cr',
        'thresholds', 'params', 'is_active',
    ];

    protected $casts = [
        'matrix' => 'array',
        'sub_matrix' => 'array',
        'weights' => 'array',
        'sub_weights' => 'array',
        'thresholds' => 'array',
        'params' => 'array',
        'cr' => 'float',
        'sub_cr' => 'float',
        'is_active' => 'boolean',
    ];

    public function riskScores(): HasMany
    {
        return $this->hasMany(RiskScore::class);
    }

    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }
}
