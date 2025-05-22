<?php

namespace App\Filament\Resources\PembelianResource\Widgets;

use App\Filament\Resources\PembelianResource\Pages\ListPembelians;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PembelianOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListPembelians::class;
    }

    protected function getStats(): array
    {
        $query = $this->getPageTableQuery();

        $totalhargaPembelian = $query->sum('total_harga');
        $count = $query->count();
        $pengeluaran = $query->withSum('pembayaran', 'total_bayar')->get()->sum('pembayaran_sum_total_bayar');
        $kembalian = $query->get()->sum(fn($pembelian) => $pembelian->uang_kembalian);
        $utang = $query->get()->sum(fn($pembelian) => $pembelian->sisa_pembayaran);

        return [
            Stat::make('Total Pembelian', $count)
                ->description('All Time')
                ->color('primary')
                ->icon('heroicon-o-shopping-cart'),

            Stat::make('Total Harga Pembelian', 'Rp. ' . number_format($totalhargaPembelian, 0, ',', '.'))
                ->description('All Time')
                ->color('primary')
                ->icon('heroicon-o-chart-bar'),

            Stat::make('Total Pengeluaran', 'Rp. ' . number_format(($pengeluaran - $kembalian), 0, ',', '.'))
                ->description('All Time')
                ->color('primary')
                ->icon('heroicon-o-currency-dollar'),

            Stat::make('Total Utang', 'Rp. ' . number_format($utang, 0, ',', '.'))
                ->description('All Time')
                ->color('primary')
                ->icon('heroicon-o-exclamation-circle'),
        ];
    }
}
