<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Regency extends Model
{
    use HasFactory;

    protected $fillable = [
        'province', 'code', 'name', 'slug', 'geometry', 'centroid_lat', 'centroid_lng',
    ];

    protected $casts = [
        'geometry' => 'array',
        'centroid_lat' => 'float',
        'centroid_lng' => 'float',
    ];

    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
