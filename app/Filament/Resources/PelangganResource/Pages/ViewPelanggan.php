<?php

namespace App\Filament\Resources\PelangganResource\Pages;

use App\Filament\Resources\PelangganResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPelanggan extends ViewRecord
{
    protected static string $resource = PelangganResource::class;
    protected static ?string $navigationLabel = 'Detail Pelanggan';
    protected static ?string $title = 'Detail Pelanggan';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
