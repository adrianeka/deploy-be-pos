<?php

namespace App\Filament\Resources\PenerimaZakatResource\Pages;

use App\Filament\Resources\PenerimaZakatResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPenerimaZakat extends EditRecord
{
    protected static string $resource = PenerimaZakatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
