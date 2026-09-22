<?php

namespace App\Enums;

enum UserRole: string
{
    case Tamu = 'tamu';
    case Warga = 'warga';
    case Verifikator = 'verifikator';
    case Admin = 'admin';
    case Superadmin = 'superadmin';

    public function label(): string
    {
        return match ($this) {
            self::Tamu => 'Tamu',
            self::Warga => 'Warga',
            self::Verifikator => 'Verifikator',
            self::Admin => 'Admin',
            self::Superadmin => 'Superadmin',
        };
    }

    public function isStaf(): bool
    {
        return in_array($this, [self::Verifikator, self::Admin, self::Superadmin], true);
    }

    public function isAdminPanel(): bool
    {
        return in_array($this, [self::Admin, self::Superadmin], true);
    }
}
