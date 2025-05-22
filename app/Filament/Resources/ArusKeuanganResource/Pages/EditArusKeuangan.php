<?php

namespace App\Filament\Resources\ArusKeuanganResource\Pages;

use App\Filament\Resources\ArusKeuanganResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditArusKeuangan extends EditRecord
{
    protected static string $resource = ArusKeuanganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
