<?php

namespace App\Filament\Resources\PelangganResource\Pages;

use App\Filament\Resources\PelangganResource;
use App\Models\Penjualan;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RiwayatTransaksi extends ManageRelatedRecords
{
    protected static string $resource = PelangganResource::class;
    protected static string $relationship = 'penjualan'; // Sesuaikan dengan relasi yang benar
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Riwayat Transaksi';
    protected static ?string $title = 'Riwayat Transaksi Pelanggan';

    public function getBreadcrumb(): string
    {
        return 'Riwayat Transaksi';
    }

    public function table(Table $table): Table
    {
        $id_pelanggan = $this->record->id_pelanggan; // Ambil ID pelanggan yang sedang dipilih

        return $table
            ->query(
                Penjualan::query()
                    ->where('id_pelanggan', $id_pelanggan) // Ambil transaksi berdasarkan pelanggan
            )
            ->defaultSort('tanggal_penjualan', 'desc')
            ->columns([
                TextColumn::make('id_penjualan')
                    ->label('Nomor Invoice')
                    ->searchable(),
                TextColumn::make('kasir.nama')
                    ->label('Kasir yang Melayani')
                    ->searchable(),
                TextColumn::make('total_pembayaran')
                    ->label('Total Bayar')
                    ->formatStateUsing(fn($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : '-'),
                TextColumn::make('sisa_pembayaran')
                    ->label('Sisa Bayar')
                    ->formatStateUsing(fn($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : '-'),
                TextColumn::make('tanggal_penjualan')
                    ->label('Tanggal')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i'))
                    ->sortable(),
                TextColumn::make('status_penjualan')
                    ->label('Status Transaksi')
                    ->badge()
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
            ->actions([])
            ->bulkActions([])
            ->headerActions([]);
    }
}
