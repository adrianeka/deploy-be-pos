<?php

namespace App\Filament\Resources\RiwayatPenjualanResource\Pages;

use App\Filament\Resources\RiwayatPenjualanResource;
use App\Filament\Resources\RiwayatPenjualanResource\Widgets\PenjualanOverview;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListRiwayatPenjualans extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = RiwayatPenjualanResource::class;
    protected static ?string $title = 'Daftar Transaksi Penjualan';


    protected function getHeaderWidgets(): array
    {
        return [
            PenjualanOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Semua' => Tab::make('Semua')
                ->icon('heroicon-o-bars-3-bottom-left'),

            'Lunas' => Tab::make('Lunas')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('penjualan.status_penjualan', 'lunas'))
                ->icon('heroicon-o-check-circle'),

            'Belum Lunas' => Tab::make('Belum Lunas')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('penjualan.status_penjualan', 'belum lunas'))
                ->icon('heroicon-o-x-circle'),

            'Pesanan' => Tab::make('Pesanan')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('penjualan.status_penjualan', 'pesanan'))
                ->icon('heroicon-o-chat-bubble-oval-left-ellipsis'),
        ];
    }
}
