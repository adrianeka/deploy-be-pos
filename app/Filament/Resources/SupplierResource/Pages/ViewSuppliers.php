<?php

namespace App\Filament\Resources\ProdukResource\Pages;

use App\Filament\Resources\SupplierResource;
use App\Models\Supplier;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewSupplier extends ViewRecord
{
    protected static string $resource = SupplierResource::class;

    public function getTitle(): string | Htmlable
    {
        /** @var Supplier */
        $record = $this->getRecord();
        return $record->nama_perusahaan;
    }

    protected function getActions(): array
    {
        return [];
    }
}
