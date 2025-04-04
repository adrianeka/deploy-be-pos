<?php

namespace App\Filament\Resources\PembelianResource\Pages;

use App\Filament\Resources\PembelianResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;
// use Filament\Resources\Components\Layout\Section;
use Filament\Forms\Components\Section;


class ViewPembelian extends ViewRecord
{
    protected static string $resource = PembelianResource::class;
    protected static ?string $navigationLabel = 'Detail Transaksi Pembelian';
    protected static ?string $title = 'Detail Transaksi Pembelian';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
