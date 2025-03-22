<?php

namespace App\Filament\Resources\PemasokResource\Pages;

use App\Filament\Resources\PemasokResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPemasoks extends ListRecords
{
    protected static string $resource = PemasokResource::class;
    protected static ?string $title = 'Daftar Pemasok';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Pemasok'),
        ];
    }
}
