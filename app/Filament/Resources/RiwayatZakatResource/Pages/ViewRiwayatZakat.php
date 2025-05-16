<?php

namespace App\Filament\Resources\RiwayatZakatResource\Pages;

use App\Filament\Resources\RiwayatZakatResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRiwayatZakat extends ViewRecord
{
    protected static string $resource = RiwayatZakatResource::class;
    protected static ?string $navigationLabel = 'Detail Riwayat Zakat';
    protected static ?string $title = 'Detail Riwayat Zakat';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            \App\Filament\Resources\RiwayatZakatResource\RelationManagers\PenjualanRelationManager::class,
        ];
    }
}
