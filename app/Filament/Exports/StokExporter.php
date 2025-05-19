<?php

namespace App\Filament\Exports;

use App\Models\Stok;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class StokExporter extends Exporter
{
    protected static ?string $model = Stok::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('produk.nama_produk')
                ->label('Produk'),
            ExportColumn::make('stok_tersedia')
                ->label('Stok Tersedia')
                ->state(function (Stok $record) {
                    return Stok::getStokTersediaByProduk($record->id_produk);
                }),,
            ExportColumn::make('produk.satuan.nama_satuan')
                ->label('Satuan'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your stok export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
