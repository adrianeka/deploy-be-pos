<?php

namespace App\Filament\Resources\PembelianResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PembelianDetailRelationManager extends RelationManager
{
    protected static string $relationship = 'pembelianDetail';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('produk.nama_produk')
                    ->label('Nama Produk'),

                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah'),

                Tables\Columns\TextColumn::make('harga_satuan')
                    ->label('Harga Satuan')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->state(fn($record) => $record->jumlah * $record->harga_satuan)
                    ->money('IDR'),
            ])
            ->paginated(false)
            ->striped();
    }
}
