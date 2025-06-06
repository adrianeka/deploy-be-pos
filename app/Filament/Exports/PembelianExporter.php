<?php

namespace App\Filament\Exports;

use App\Models\Pembelian;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PembelianExporter extends Exporter
{
    protected static ?string $model = Pembelian::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('pembelian.created_at')
                ->label('Tanggal Pembelian')
                ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i')),
            ExportColumn::make('pemasok.nama_perusahaan')
                ->label('Nama Perusahaan Pemasok'),
            ExportColumn::make('total_harga')
                ->label('Total Harga')
                ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your pembelian export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
