<?php

namespace App\Filament\Resources\RiwayatPenjualanResource\Pages;

use App\Filament\Resources\RiwayatPenjualanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRiwayatPenjualan extends ViewRecord
{
    protected static string $resource = RiwayatPenjualanResource::class;
    protected static ?string $navigationLabel = 'Detail Riwayat Penjualan';
    protected static ?string $title = 'Detail Riwayat Penjualan';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
