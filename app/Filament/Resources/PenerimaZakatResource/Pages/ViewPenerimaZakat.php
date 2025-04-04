<?php

namespace App\Filament\Resources\PenerimaZakatResource\Pages;

use App\Filament\Resources\PenerimaZakatResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPenerimaZakat extends ViewRecord
{
    protected static string $resource = PenerimaZakatResource::class;
    protected static ?string $navigationLabel = 'Detail Penerima Zakat';
    protected static ?string $title = 'Detail Penerima Zakat';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
