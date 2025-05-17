<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum StatusTransaksiPembelian: string implements HasColor, HasIcon, HasLabel
{
    case Lunas = 'lunas';

    case Diproses = 'diproses';

    case BelumLunas = 'belum lunas';

    public function getLabel(): string
    {
        return match ($this) {
            self::Lunas => 'Lunas',
            self::Diproses => 'Diproses',
            self::BelumLunas => 'Belum Lunas',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Diproses => 'warning',
            self::Lunas => 'primary',
            self::BelumLunas => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::BelumLunas => 'heroicon-m-exclamation-circle',
            self::Lunas => 'heroicon-m-check-badge',
            self::Diproses => 'heroicon-m-clock',
        };
    }
}
