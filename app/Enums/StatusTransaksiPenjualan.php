<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum StatusTransaksiPenjualan: string implements HasColor, HasIcon, HasLabel
{
    case Lunas = 'lunas';

    case BelumLunas = 'belum lunas';

    case Pesanan = 'pesanan';

    public function getLabel(): string
    {
        return match ($this) {
            self::Lunas => 'Lunas',
            self::BelumLunas => 'Belum Lunas',
            self::Pesanan => 'Pesanan',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Pesanan => 'warning',
            self::Lunas => 'primary',
            self::BelumLunas => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::BelumLunas => 'heroicon-m-exclamation-circle',
            self::Pesanan => 'heroicon-m-truck',
            self::Lunas => 'heroicon-m-check-badge',
        };
    }
}
