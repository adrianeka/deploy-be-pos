<?php

namespace App\Filament\Resources\KocakResource\Pages;

use App\Filament\Resources\KocakResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKocaks extends ListRecords
{
    protected static string $resource = KocakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
