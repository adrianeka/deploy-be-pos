<?php

namespace App\Filament\Resources\PembayaranZakatResource\Pages;

use App\Filament\Resources\PembayaranZakatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPembayaranZakats extends ListRecords
{
    protected static string $resource = PembayaranZakatResource::class;
    public int $selectedCount = 0;
    public float $totalModal = 0;
    public float $totalZakat = 0;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
