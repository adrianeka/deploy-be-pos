<?php

namespace App\Filament\Resources\PembelianResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

class PembelianDetailRelationManager extends RelationManager
{
    protected static string $relationship = 'pembelianDetail'; // Pastikan relasi ini sesuai dengan Model

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('produk.nama_produk')
                    ->label('Nama Produk'),
                TextColumn::make('jumlah_produk')
                    ->label('Jumlah'),
                TextColumn::make('produk.harga_beli')
                    ->label('Harga'),
                TextColumn::make('total_harga')
                    ->label('Total Harga')
                    ->getStateUsing(function ($record) {
                        return 'Rp. ' . number_format($record->total_harga, 0, ',', '.');
                    })
            ]);
    }
}
