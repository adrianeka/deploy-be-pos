<?php

namespace App\Filament\Resources\PenerimaZakatResource\Pages;

use App\Filament\Resources\PenerimaZakatResource;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class RiwayatZakat extends ManageRelatedRecords
{
    protected static string $resource = PenerimaZakatResource::class;
    protected static string $relationship = 'bayar_zakat';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Riwayat Zakat';
    protected static ?string $title = 'Riwayat Zakat';

    public function getBreadcrumb(): string
    {
        return 'Riwayat Transaksi';
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal_bayar', 'desc')
            ->columns([
                TextColumn::make('tanggal_bayar')
                    ->label('Tanggal Bayar')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('modal_terjual')
                    ->label('Modal yang terjual')
                    ->searchable()
                    ->formatStateUsing(fn($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : '-')
                    ->sortable(),

                TextColumn::make('nominal_zakat')
                    ->label('Nominal Zakat Sebesar 2.5%')
                    ->searchable()
                    ->formatStateUsing(fn($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : '-')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Action::make('view')
                    ->url(fn() => '#', shouldOpenInNewTab: false)
                    ->icon('heroicon-o-eye'),
            ])
            ->bulkActions([])
            ->headerActions([]);
    }
}
