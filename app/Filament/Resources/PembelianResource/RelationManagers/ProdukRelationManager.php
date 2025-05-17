<?php

namespace App\Filament\Resources\PembelianResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProdukRelationManager extends RelationManager
{
    protected static string $relationship = 'pembelianDetail';

    protected static ?string $title = 'Data Produk';

    public static function modifyQueryUsing($query)
    {
        return $query->with(['produk', 'pembelian']);
    }

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('produk.nama_produk')->label('Nama Produk'),
                Tables\Columns\TextColumn::make('jumlah_produk')->label('Jumlah Produk'),
                Tables\Columns\TextColumn::make('harga_beli')
                    ->label('Harga Beli Produk')
                    ->getStateUsing(fn($record) => $record->produk?->harga_beli ?? 0)
                    ->formatStateUsing(fn($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : '-'),

                Tables\Columns\TextColumn::make('total_harga_item')
                    ->label('Total Harga Produk')
                    ->getStateUsing(function ($record) {
                        $hargaBeli = $record->produk?->harga_beli ?? 0;
                        return $hargaBeli * $record->jumlah_produk;
                    })
                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),
            ]);
    }
}
