<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Baru = 'baru';
    case Ditinjau = 'ditinjau';
    case Terverifikasi = 'terverifikasi';
    case Ditolak = 'ditolak';
    case Diteruskan = 'diteruskan';
    case Selesai = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::Baru => 'Baru',
            self::Ditinjau => 'Ditinjau',
            self::Terverifikasi => 'Terverifikasi',
            self::Ditolak => 'Ditolak',
            self::Diteruskan => 'Diteruskan ke instansi',
            self::Selesai => 'Selesai',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Baru => 'bg-leaf-100 text-ink-500',
            self::Ditinjau => 'bg-risk-mid/30 text-[#4A3B00]',
            self::Terverifikasi => 'bg-risk-low/30 text-forest-800',
            self::Ditolak => 'bg-risk-extreme/15 text-risk-extreme',
            self::Diteruskan => 'bg-water/40 text-forest-800',
            self::Selesai => 'bg-forest-800 text-white',
        };
    }
}
