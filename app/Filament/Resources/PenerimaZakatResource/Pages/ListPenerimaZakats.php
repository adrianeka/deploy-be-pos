<?php

namespace App\Filament\Resources\PenerimaZakatResource\Pages;

use App\Filament\Resources\PenerimaZakatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPenerimaZakats extends ListRecords
{
    protected static string $resource = PenerimaZakatResource::class;
    protected static ?string $title = 'Daftar Penerima Zakat';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Penerima Zakat'),
        ];
    }
}
