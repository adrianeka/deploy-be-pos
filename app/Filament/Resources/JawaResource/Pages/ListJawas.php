<?php

namespace App\Filament\Resources\JawaResource\Pages;

use App\Filament\Resources\JawaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJawas extends ListRecords
{
    protected static string $resource = JawaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
