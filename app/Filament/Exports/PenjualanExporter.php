<?php

namespace App\Filament\Exports;

use App\Models\Penjualan;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PenjualanExporter extends Exporter
{
    protected static ?string $model = Penjualan::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id_penjualan')
                ->label("Nomor Invoice"),
            ExportColumn::make('created_at')
                ->label("Tanggal Penjualan")
                ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i')),
            ExportColumn::make('pelanggan.nama_pelanggan')
                ->label("Pelanggan"),
            ExportColumn::make('kasir.nama')
                ->label("Kasir"),
            ExportColumn::make('total_harga')
                ->label("Total Harga")
                ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),
            ExportColumn::make('status_penjualan')->formatStateUsing(fn($state) => $state->value)
                ->label("Status Penjualan"),
            ExportColumn::make('uangDiterima')
                ->label("Total Bayar")
                ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),
            ExportColumn::make('uangKembalian')
                ->label("Total Kembalian")
                ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),
            ExportColumn::make('sisaPembayaran')
                ->label("Sisa Bayar")
                ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor selesai! ' . number_format($export->successful_rows) . ' baris berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal diekspor.';
        }

        return $body;
    }
}
