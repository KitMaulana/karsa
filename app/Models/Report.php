<?php

namespace App\Models;

use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'district_id', 'lat', 'lng', 'accuracy_m', 'type', 'description',
        'media', 'exif', 'phash', 'trust_score', 'flags', 'status', 'verified_by',
        'verified_at', 'rejection_reason', 'forwarded_at', 'is_demo',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'accuracy_m' => 'float',
        'media' => 'array',
        'exif' => 'array',
        'flags' => 'array',
        'trust_score' => 'float',
        'status' => ReportStatus::class,
        'verified_at' => 'datetime',
        'forwarded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(ReportStatusLog::class);
    }
}
