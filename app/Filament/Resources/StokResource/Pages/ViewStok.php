<?php

namespace App\Filament\Resources\StokResource\Pages;

use App\Filament\Resources\StokResource;
use Filament\Resources\Pages\ViewRecord;

class ViewStok extends ViewRecord
{
    protected static string $resource = StokResource::class;
    protected static ?string $navigationLabel = 'Detail Stok';
    protected static ?string $title = 'Detail Stok Produk';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
