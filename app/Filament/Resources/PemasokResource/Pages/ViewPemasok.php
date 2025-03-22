<?php

namespace App\Filament\Resources\PemasokResource\Pages;

use App\Filament\Resources\PemasokResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPemasok extends ViewRecord
{
    protected static string $resource = PemasokResource::class;
    protected static ?string $navigationLabel = 'Detail Pemasok';
    protected static ?string $title = 'Detail Pemasok';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
