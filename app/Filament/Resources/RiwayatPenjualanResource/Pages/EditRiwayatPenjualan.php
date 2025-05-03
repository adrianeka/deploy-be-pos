<?php

namespace App\Filament\Resources\RiwayatPenjualanResource\Pages;

use App\Filament\Resources\RiwayatPenjualanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRiwayatPenjualan extends EditRecord
{
    protected static string $resource = RiwayatPenjualanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
