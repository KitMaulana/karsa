<?php

namespace App\Enums;

enum HotspotConfidence: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';

    public static function fromRaw(string|int|float|null $raw): self
    {
        if ($raw === null) {
            return self::Medium;
        }

        if (is_numeric($raw)) {
            $value = (float) $raw;

            return match (true) {
                $value >= 80 => self::High,
                $value >= 31 => self::Medium,
                default => self::Low,
            };
        }

        $normalized = strtolower(trim((string) $raw));

        return match (true) {
            in_array($normalized, ['h', 'high', 'tinggi'], true) => self::High,
            in_array($normalized, ['l', 'low', 'rendah'], true) => self::Low,
            default => self::Medium,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Rendah',
            self::Medium => 'Sedang',
            self::High => 'Tinggi',
        };
    }

    public function weight(): float
    {
        return match ($this) {
            self::Low => (float) config('karsa.risk.confidence_weight.low', 0.3),
            self::Medium => (float) config('karsa.risk.confidence_weight.medium', 0.6),
            self::High => (float) config('karsa.risk.confidence_weight.high', 1.0),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Low => '#F5D35C',
            self::Medium => '#F28C38',
            self::High => '#D63B2F',
        };
    }
}
