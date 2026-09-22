<?php

namespace App\Enums;

enum RiskLevel: string
{
    case Rendah = 'rendah';
    case Sedang = 'sedang';
    case Tinggi = 'tinggi';
    case SangatTinggi = 'sangat_tinggi';

    public static function fromScore(float $score, ?array $thresholds = null): self
    {
        $thresholds ??= config('karsa.risk.thresholds');

        foreach ($thresholds as $key => [$min, $max]) {
            if ($score >= $min && $score <= $max) {
                return self::from($key);
            }
        }

        return $score >= 85 ? self::SangatTinggi : self::Rendah;
    }

    public function label(): string
    {
        return match ($this) {
            self::Rendah => 'Rendah',
            self::Sedang => 'Sedang',
            self::Tinggi => 'Tinggi',
            self::SangatTinggi => 'Sangat tinggi',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Rendah => '#7BC96F',
            self::Sedang => '#F5D35C',
            self::Tinggi => '#F28C38',
            self::SangatTinggi => '#D63B2F',
        };
    }

    public function textColor(): string
    {
        return $this === self::Sedang ? '#4A3B00' : '#FFFFFF';
    }

    public function rank(): int
    {
        return match ($this) {
            self::Rendah => 0,
            self::Sedang => 1,
            self::Tinggi => 2,
            self::SangatTinggi => 3,
        };
    }

    public function isHigherThan(self $other): bool
    {
        return $this->rank() > $other->rank();
    }
}
