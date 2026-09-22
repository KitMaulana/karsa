<?php

namespace App\Models;

use App\Enums\RiskLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthorityContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'agency', 'regency_id', 'district_id', 'email', 'whatsapp', 'min_level',
    ];

    protected $casts = [
        'min_level' => RiskLevel::class,
    ];

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
