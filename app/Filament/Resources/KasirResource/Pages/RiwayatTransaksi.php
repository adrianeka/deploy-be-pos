<?php

namespace App\Filament\Resources\KasirResource\Pages;

use App\Filament\Resources\KasirResource;
use App\Models\Penjualan;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class RiwayatTransaksi extends ManageRelatedRecords
{
    protected static string $resource = KasirResource::class;
    protected static string $relationship = 'penjualan';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Riwayat Transaksi';
    protected static ?string $title = 'Riwayat Transaksi Kasir';

    public function getBreadcrumb(): string
    {
        return 'Riwayat Transaksi';
    }

    public function table(Table $table): Table
    {
        $id_kasir = $this->record->id_kasir;

        return $table
            ->query(
                Penjualan::query()
                    ->where('id_kasir', $id_kasir)
                    ->withSum('pembayaran', 'total_bayar')
            )
            ->defaultSort('tanggal_penjualan', 'desc')
            ->columns([
                TextColumn::make('tanggal_penjualan')
                    ->label('Tanggal')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('id_penjualan')
                    ->label('Nomor Invoice')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status_penjualan')
                    ->label('Status Transaksi')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->color(fn(string $state): string => match ($state) {
                        'Lunas' => 'success',
                        'Belum Lunas' => 'danger',
                        'Pesanan' => 'warning',
                    }),
            ])
            ->filters([
                SelectFilter::make('status_penjualan')
                    ->label('Filter Status Transaksi')
                    ->options([
                        'Lunas' => 'Lunas',
                        'Belum Lunas' => 'Belum Lunas',
                        'Pesanan' => 'Pesanan',
                    ]),
            ])
            ->actions([
                Action::make('view')
                    ->url(fn() => '#', shouldOpenInNewTab: false)
                    ->icon('heroicon-o-eye'),
            ])
            ->bulkActions([])
            ->headerActions([]);
    }
}
