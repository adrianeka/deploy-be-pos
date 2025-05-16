<?php

namespace App\Filament\Resources\PembelianResource\RelationManagers;

use App\Models\MetodePembayaran;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Forms\Form;
use Filament\Forms;

class PembayaranRelationManager extends RelationManager
{
    protected static string $relationship = 'pembayaranPembelian';

    protected static ?string $title = 'Data Pembayaran';

    public static function modifyQueryUsing($query)
    {
        return $query->with(['pembayaranPembelian', 'pembelian']);
    }

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pembayaran.metode_pembayaran.jenis_pembayaran')
                    ->label('Metode Pembayaran')
                    ->formatStateUsing(fn($state) => $state ? Str::ucfirst($state) : '-'),

                Tables\Columns\TextColumn::make('pembayaran.metode_pembayaran.tipe_transfer.metode_transfer')
                    ->label('Tipe Transfer')
                    ->getStateUsing(fn($record) => $record->pembayaran?->metode_pembayaran?->tipe_transfer?->metode_transfer)
                    ->formatStateUsing(fn($state) => $state ? Str::ucfirst($state) : '-'),

                Tables\Columns\TextColumn::make('pembayaran.metode_pembayaran.tipe_transfer.jenis_transfer')
                    ->label('Jenis Transfer')
                    ->getStateUsing(fn($record) => $record->pembayaran?->metode_pembayaran?->tipe_transfer?->jenis_transfer)
                    ->formatStateUsing(fn($state) => $state ?: '-'),


                Tables\Columns\TextColumn::make('pembayaran.total_bayar')
                    ->label('Total Bayar')
                    ->formatStateUsing(fn($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : '-'),

                Tables\Columns\TextColumn::make('pembayaran.keterangan')
                    ->label('Keterangan'),

                Tables\Columns\TextColumn::make('pembayaran.tanggal_pembayaran')
                    ->label('Tanggal')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i')),
            ]);
    }
}
