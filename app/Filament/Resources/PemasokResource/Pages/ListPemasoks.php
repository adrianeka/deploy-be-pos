<?php

namespace App\Filament\Resources\PemasokResource\Pages;

use App\Filament\Resources\PemasokResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPemasoks extends ListRecords
{
    // Set resource class statis untuk kemudahan akses
    protected static string $resource = PemasokResource::class;

    // Gunakan konstanta untuk judul halaman agar tidak dievaluasi berulang
    protected const TITLE = 'Daftar Pemasok';

    // Override judul dengan konstanta
    protected static ?string $title = self::TITLE;

    // Gunakan method untuk mendapatkan aksi header
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Pemasok'),
        ];
    }
}
