<?php

namespace App\Filament\Resources\RiwayatPenjualanResource\Pages;

use App\Filament\Resources\RiwayatPenjualanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListRiwayatPenjualans extends ListRecords
{
    protected static string $resource = RiwayatPenjualanResource::class;
    protected static ?string $title = 'Daftar Riwayat Transaksi Penjualan';

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    public function getTabs(): array
    {
        return [
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
