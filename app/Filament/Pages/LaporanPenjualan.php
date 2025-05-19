<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class LaporanPenjualan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.laporan-penjualan';

    public static function canAccess(): bool
    {
        return auth()->user()->role === 'pemilik';
    }

    protected function getHeaderWidgets(): array
    {
        return [
        ];
    }
}


