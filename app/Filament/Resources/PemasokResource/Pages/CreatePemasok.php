<?php

namespace App\Filament\Resources\PemasokResource\Pages;

use App\Filament\Resources\PemasokResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePemasok extends CreateRecord
{
    protected static string $resource = PemasokResource::class;
    protected static ?string $title = 'Tambah Pemasok';
}
