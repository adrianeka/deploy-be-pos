<?php

namespace App\Filament\Resources\ProdukResource\Pages;

use App\Filament\Resources\ProdukResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListProduks extends ListRecords
{
    protected static string $resource = ProdukResource::class;
    protected static ?string $title = 'Daftar Produk';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Produk'),
        ];
    }
}
