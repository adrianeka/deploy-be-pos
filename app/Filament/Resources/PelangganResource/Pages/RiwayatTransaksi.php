<?php

namespace App\Filament\Resources\PelangganResource\Pages;

use App\Filament\Resources\PelangganResource;
use App\Models\Penjualan;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class RiwayatTransaksi extends ManageRelatedRecords
{
    protected static string $resource = PelangganResource::class;
    protected static string $relationship = 'penjualan';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Riwayat Transaksi';
    protected static ?string $title = 'Riwayat Transaksi Pelanggan';

    public function getBreadcrumb(): string
    {
        return 'Riwayat Transaksi';
    }

    public function table(Table $table): Table
    {
        $id_pelanggan = $this->record->id_pelanggan;

        return $table
            ->query(
                Penjualan::query()
                    ->where('id_pelanggan', $id_pelanggan)
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('id_penjualan')
                    ->label('Nomor Invoice')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kasir.nama')
                    ->label('Kasir yang Melayani')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status_penjualan')
                    ->label('Status Transaksi')
                    ->badge()
                    ->searchable()
                    ->sortable()
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
                Action::make('Lihat')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => route('filament.admin.resources.riwayat-penjualan.view', ['record' => $record->id_penjualan]))
            ])
            ->bulkActions([])
            ->headerActions([]);
    }
}
