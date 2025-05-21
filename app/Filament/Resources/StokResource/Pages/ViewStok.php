<?php

namespace App\Filament\Resources\StokResource\Pages;

use App\Filament\Resources\StokResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewStok extends ViewRecord
{
    protected static string $resource = StokResource::class;
    protected static ?string $title = 'Detail Stok Produk';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Edit Stok')
                ->url(StokResource::getUrl('create'))
                ->color('primary')
        ];
    }
}
