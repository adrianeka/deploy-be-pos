<?php

namespace App\Filament\Resources\PembelianResource\Pages;

use App\Filament\Resources\PembelianResource;
use App\Filament\Resources\PembelianResource\Widgets\PembelianOverview;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPembelians extends ListRecords
{
    use ExposesTableToWidgets;
    protected static string $resource = PembelianResource::class;
    protected static ?string $title = 'Daftar Transaksi Pembelian';

    protected function getHeaderWidgets(): array
    {
        return [
            PembelianOverview::class,
        ];
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Transaksi Pembelian'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Semua' => Tab::make('Semua')
                ->icon('heroicon-o-clock'),
            'Diproses' => Tab::make('Diproses')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('pembelian.status_pembelian', 'Diproses'))
                ->icon('heroicon-o-clock'),
            'Lunas' => Tab::make('Lunas')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('pembelian.status_pembelian', 'Lunas'))
                ->icon('heroicon-o-check-circle'),
            'Belum Lunas' => Tab::make('Belum Lunas')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('pembelian.status_pembelian', 'Belum Lunas'))
                ->icon('heroicon-o-x-circle'),
        ];
    }
}
