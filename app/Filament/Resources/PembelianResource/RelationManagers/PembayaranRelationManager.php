<?php

namespace App\Filament\Resources\PembelianResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PembayaranRelationManager extends RelationManager
{
    protected static string $relationship = 'pembayaran'; // nama relasi di model Pembelian

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('metodePembayaran.jenis_pembayaran')
                    ->label('Jenis Pembayaran'),

                Tables\Columns\TextColumn::make('metodePembayaran.tipeTransfer.jenis_transfer')
                    ->label('Bank / E-Money')
                    ->default('-'),

                Tables\Columns\TextColumn::make('tanggal_pembayaran')
                    ->label('Tanggal Pembayaran')
                    ->dateTime('d M Y, H:i'),

                Tables\Columns\TextColumn::make('total_bayar')
                    ->label('Total Bayar')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->default('-'),
            ])
            ->headerActions([])
            ->paginated(false)
            ->striped();
    }
}
