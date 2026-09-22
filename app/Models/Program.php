<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'description', 'cover', 'starts_at', 'location',
        'volunteer_quota', 'donation_enabled', 'donation_target', 'donation_collected',
        'payment_info', 'usage_report', 'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'donation_enabled' => 'boolean',
        'donation_target' => 'float',
        'donation_collected' => 'float',
        'payment_info' => 'array',
    ];

    public function participants(): HasMany
    {
        return $this->hasMany(ProgramParticipant::class);
    }

    public function volunteers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'program_participants')->withPivot('status')->withTimestamps();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isFull(): bool
    {
        if ($this->volunteer_quota === null) {
            return false;
        }

        return $this->participants()->where('status', '!=', 'batal')->count() >= $this->volunteer_quota;
    }

    public function donationProgressPercent(): float
    {
        if (! $this->donation_target) {
            return 0;
        }

        return round(min(100, ($this->donation_collected / $this->donation_target) * 100), 1);
    }
}
