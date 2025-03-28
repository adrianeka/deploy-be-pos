<?php

namespace App\Filament\Resources\JawaResource\Pages;

use App\Filament\Resources\JawaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJawa extends EditRecord
{
    protected static string $resource = JawaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
