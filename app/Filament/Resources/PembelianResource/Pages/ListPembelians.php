<?php

namespace App\Filament\Resources\PembelianResource\Pages;

use App\Filament\Resources\PembelianResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPembelians extends ListRecords
{
    protected static string $resource = PembelianResource::class;
    protected static ?string $title = 'Daftar Transaksi Pembelian';

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
            'Diproses' => Tab::make('Diproses')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('pembelian.status_pembelian', 'Diproses')),
            'Lunas' => Tab::make('Lunas')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('pembelian.status_pembelian', 'Lunas')),
            'Belum Lunas' => Tab::make('Belum Lunas')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('pembelian.status_pembelian', 'Belum Lunas')),
        ];
    }
}
