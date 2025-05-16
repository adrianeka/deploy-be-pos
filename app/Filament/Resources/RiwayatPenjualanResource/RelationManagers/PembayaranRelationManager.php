<?php

namespace App\Filament\Resources\RiwayatPenjualanResource\RelationManagers;

use Filament\Forms;
use App\Models\MetodePembayaran;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PembayaranRelationManager extends RelationManager
{
    protected static string $relationship = 'pembayaranPenjualan';

    protected static ?string $title = 'Data Pembayaran';

    public function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pembayaran.metode_pembayaran.jenis_pembayaran')
                    ->label('Metode Pembayaran')
                    ->formatStateUsing(fn($state) => Str::ucfirst($state)),

                Tables\Columns\TextColumn::make('pembayaran.metode_pembayaran.tipe_transfer.metode_transfer')
                    ->label('Tipe Transfer')
                    ->formatStateUsing(fn($state) => Str::ucfirst($state)),

                Tables\Columns\TextColumn::make('pembayaran.metode_pembayaran.tipe_transfer.jenis_transfer')
                    ->label('Jenis Transfer'),

                Tables\Columns\TextColumn::make('pembayaran.total_bayar')
                    ->label('Total Bayar')
                    ->formatStateUsing(fn($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : '-'),

                Tables\Columns\TextColumn::make('pembayaran.keterangan')
                    ->label('Keterangan'),

                Tables\Columns\TextColumn::make('pembayaran.created_at')
                    ->label('Tanggal')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
