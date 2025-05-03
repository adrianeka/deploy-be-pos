<?php

namespace App\Filament\Resources\PembelianResource\Pages;

use App\Filament\Resources\PembelianResource;
use App\Filament\Resources\PembelianResource\RelationManagers\PembayaranRelationManager;
use App\Filament\Resources\PembelianResource\RelationManagers\PembelianDetailRelationManager;
use Filament\Resources\Pages\ViewRecord;

class ViewTransaksiPembelian extends ViewRecord
{
    protected static string $resource = PembelianResource::class;

    public function getRelationManagers(): array
    {
        return [
            PembelianDetailRelationManager::class,
            PembayaranRelationManager::class,
        ];
    }
}
