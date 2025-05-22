<?php

namespace App\Filament\Resources\RiwayatPenjualanResource\Widgets;

use App\Filament\Resources\RiwayatPenjualanResource\Pages\ListRiwayatPenjualans;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PenjualanOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListRiwayatPenjualans::class;
    }

    protected function getStats(): array
    {
        $query = $this->getPageTableQuery();

        $omset = $query->sum('total_harga');
        $count = $query->count();
        $pendapatan = $query->withSum('pembayaran', 'total_bayar')->get()->sum('pembayaran_sum_total_bayar');
        $kembalian = $query->get()->sum(function ($penjualan) {
            return $penjualan->uang_kembalian;
        });
        $piutang = $query->get()->sum(function ($penjualan) {
            return $penjualan->sisa_pembayaran;
        });

        return [
            Stat::make('Total Penjualan', $count)
                ->description('All Time')
                ->color('primary')
                ->icon('heroicon-o-shopping-cart'),

            Stat::make('Total Omset', 'Rp. ' . number_format($omset, 0, ',', '.'))
                ->description('All Time')
                ->color('primary')
                ->icon('heroicon-o-chart-bar'),

            Stat::make('Total Pendapatan', 'Rp. ' . number_format(($pendapatan - $kembalian), 0, ',', '.'))
                ->description('All Time')
                ->color('primary')
                ->icon('heroicon-o-currency-dollar'),

            Stat::make('Total Piutang', 'Rp. ' . number_format($piutang, 0, ',', '.'))
                ->description('All Time')
                ->color('primary')
                ->icon('heroicon-o-exclamation-circle'),
        ];
    }
}
