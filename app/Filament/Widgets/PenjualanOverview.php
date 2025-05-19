<?php

namespace App\Filament\Widgets;

use App\Models\Penjualan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PenjualanOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $penjualans = Penjualan::get();
        $omset = $penjualans->sum('total_harga');
        $pendapatan = $penjualans->sum('uangDiterima');
        $piutang = $penjualans->sum('sisaPembayaran');
        $count = $penjualans->count();
        return [
            Stat::make('Total Penjualan', $count)
                ->color('primary')
                ->icon('heroicon-o-shopping-cart'),

            Stat::make('Total Omset', number_format($omset, 0, ',', '.'))
                ->color('primary')
                ->icon('heroicon-o-shopping-cart'),
                
            Stat::make('Total Pendapatan', number_format($pendapatan, 0, ',', '.'))
                ->color('success')
                ->icon('heroicon-o-currency-dollar'),
                
            Stat::make('Total Piutang', number_format($piutang, 0, ',', '.'))
                ->color('danger')
                ->icon('heroicon-o-exclamation-circle')
        ];
    }
}