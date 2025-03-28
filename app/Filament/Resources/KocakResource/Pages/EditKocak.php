<?php

namespace App\Filament\Resources\KocakResource\Pages;

use App\Filament\Resources\KocakResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKocak extends EditRecord
{
    protected static string $resource = KocakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
