<?php

namespace App\Filament\Resources\RiwayatPenjualanResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProdukRelationManager extends RelationManager
{
    protected static string $relationship = 'penjualanDetail';

    public static function modifyQueryUsing($query)
    {
        return $query->with(['produk', 'penjualan']);
    }

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading("Data Produk")
            ->columns([
                Tables\Columns\TextColumn::make('produk.nama_produk')->label('Nama Produk'),
                Tables\Columns\TextColumn::make('jumlah_produk')->label('Jumlah Produk'),
                Tables\Columns\TextColumn::make('harga_jual')->label('Harga Jual Produk')
                    ->formatStateUsing(fn($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : '-'),
                Tables\Columns\TextColumn::make('total_harga_item')
                    ->label('Total Harga Produk')
                    ->getStateUsing(function ($record) {
                        return $record->harga_jual * $record->jumlah_produk;
                    })
                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),
            ]);
    }
}
