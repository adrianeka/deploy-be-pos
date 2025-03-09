<?php

namespace App\Filament\Resources\ProdukResource\Pages;

use App\Filament\Resources\ProdukResource;
use App\Models\Produk;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewProduk extends ViewRecord
{
    protected static string $resource = ProdukResource::class;

    public function getTitle(): string | Htmlable
    {
        /** @var Produk */
        $record = $this->getRecord();
        return $record->nama_produk;
    }

    protected function getActions(): array
    {
        return [];
    }
}
