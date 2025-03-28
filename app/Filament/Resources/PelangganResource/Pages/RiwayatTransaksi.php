<?php

namespace App\Filament\Resources\PelangganResource\Pages;

use App\Filament\Resources\PelangganResource;
use App\Models\Penjualan;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                    ->withSum('pembayaran', 'total_bayar')
            )
            ->defaultSort('tanggal_penjualan', 'desc')
            ->columns([
                TextColumn::make('id_penjualan')
                    ->label('Nomor Invoice')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kasir.nama')
                    ->label('Kasir yang Melayani')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_pembayaran')
                    ->label('Total Bayar')
                    ->getStateUsing(function (Penjualan $record): string {
                        $totalBayar = $record->pembayaran_sum_total_bayar ?? 0;
                        return $totalBayar ? 'Rp. ' . number_format($totalBayar, 0, ',', '.') : '-';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('pembayaran', function ($q) use ($search) {
                            $q->where('total_bayar', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('pembayaran_sum_total_bayar', $direction);
                    }),

                TextColumn::make('sisa_pembayaran')
                    ->label('Sisa Bayar')
                    ->getStateUsing(function (Penjualan $record): string {
                        $totalBayar = $record->pembayaran_sum_total_bayar ?? 0;
                        $sisaBayar = $record->total_harga - $totalBayar;
                        return $sisaBayar ? 'Rp. ' . number_format($sisaBayar, 0, ',', '.') : '-';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereRaw("(total_harga - (SELECT COALESCE(SUM(total_bayar), 0) FROM pembayaran WHERE pembayaran.id_penjualan = penjualan.id_penjualan)) LIKE ?", ["%{$search}%"]);
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("(total_harga - (SELECT COALESCE(SUM(total_bayar), 0) FROM pembayaran WHERE pembayaran.id_penjualan = penjualan.id_penjualan)) {$direction}");
                    }),

                TextColumn::make('tanggal_penjualan')
                    ->label('Tanggal')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i'))
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
            ->actions([])
            ->bulkActions([])
            ->headerActions([]);
    }
}
