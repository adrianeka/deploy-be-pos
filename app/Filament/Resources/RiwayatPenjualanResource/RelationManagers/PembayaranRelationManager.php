<?php

namespace App\Filament\Resources\RiwayatPenjualanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PembayaranRelationManager extends RelationManager
{
    protected static string $relationship = 'pembayaranPenjualan';

    public static function getLabel(): string
    {
        return 'Data Pembayaran'; // Ini fallback label untuk item tunggal
    }

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
            ->heading('Data Pembayaran')
            ->recordTitleAttribute('pembayaran.metode_pembayaran.jenis_pembayaran')
            ->columns([
                Tables\Columns\TextColumn::make('pembayaran.metode_pembayaran.jenis_pembayaran')
                    ->label('Metode Pembayaran'),

                Tables\Columns\TextColumn::make('pembayaran.metode_pembayaran.tipe_transfer.metode_transfer')
                    ->label('Tipe Transfer'),

                Tables\Columns\TextColumn::make('pembayaran.metode_pembayaran.tipe_transfer.jenis_transfer')
                    ->label('Jenis Transfer'),

                Tables\Columns\TextColumn::make('pembayaran.total_bayar')
                    ->label('Total Bayar')
                    ->formatStateUsing(fn($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : '-'),

                Tables\Columns\TextColumn::make('tanggal_pembayaran')
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
