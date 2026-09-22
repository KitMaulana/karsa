<?php

namespace App\Models;

use App\Enums\RiskLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'district_id', 'from_level', 'to_level', 'score', 'causes', 'is_manual',
        'message', 'sent_at', 'created_by', 'is_demo',
    ];

    protected $casts = [
        'from_level' => RiskLevel::class,
        'to_level' => RiskLevel::class,
        'score' => 'float',
        'causes' => 'array',
        'is_manual' => 'boolean',
        'sent_at' => 'datetime',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
