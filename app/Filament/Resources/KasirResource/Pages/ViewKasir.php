<?php

namespace App\Filament\Resources\KasirResource\Pages;

use App\Filament\Resources\KasirResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewKasir extends ViewRecord
{
    protected static string $resource = KasirResource::class;
    protected static ?string $navigationLabel = 'Detail Kasir';
    protected static ?string $title = 'Detail Kasir';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
