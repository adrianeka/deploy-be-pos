<?php

namespace App\Filament\Resources\RiwayatZakatResource\Pages;

use App\Filament\Resources\RiwayatZakatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRiwayatZakats extends ListRecords
{
    protected static string $resource = RiwayatZakatResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
