<?php

namespace App\Filament\Resources\RiwayatPenjualanResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProdukRelationManager extends RelationManager
{
    protected static string $relationship = 'penjualanDetail';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('id')
                //     ->required()
                //     ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_produk')
            ->heading("Data Produk")
            ->columns([
                Tables\Columns\TextColumn::make('produk.nama_produk')
                    ->label('Nama Produk'),

                Tables\Columns\TextColumn::make('jumlah_produk')
                    ->label('Jumlah Produk'),

                Tables\Columns\TextColumn::make('harga_jual')
                    ->label('Harga Jual')
                    ->formatStateUsing(fn($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : '-'),

                Tables\Columns\TextColumn::make('penjualan.total_harga')
                    ->label('Total Harga')
                    ->formatStateUsing(fn($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : '-'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // 
            ])
            ->actions([
                // 
            ])
            ->bulkActions([
                // 
            ]);
    }
}
