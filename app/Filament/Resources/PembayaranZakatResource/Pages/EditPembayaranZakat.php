<?php

namespace App\Filament\Resources\PembayaranZakatResource\Pages;

use App\Filament\Resources\PembayaranZakatResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPembayaranZakat extends EditRecord
{
    protected static string $resource = PembayaranZakatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
