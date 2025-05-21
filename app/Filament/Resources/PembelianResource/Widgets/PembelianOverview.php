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
        // $pembelians = Pembelian::get();
        // $count = $pembelians->count();
        // $totalhargaPembelian = $pembelians->sum('total_harga');
        // $pengeluaran = $pembelians->sum('uangBayar');
        // $kembalian = $pembelians->sum('uangKembalian');
        // $utang = $pembelians->sum('sisaPembayaran');
        $query = $this->getPageTableQuery();
    
        $totalhargaPembelian = $query->sum('total_harga');
        $count = $query->count();
        
        // Menggunakan subquery untuk menghitung nilai yang memerlukan accessor
        $pengeluaran = $query->withSum('pembayaran', 'total_bayar')->get()->sum('pembayaran_sum_total_bayar');
        
        $kembalian = $query->get()->sum(function($pembelian) {
            return $pembelian->uang_kembalian;
        });
        
        $utang = $query->get()->sum(function($pembelian) {
            return $pembelian->sisa_pembayaran;
        });
        return [
            Stat::make('Total Pembelian', $count)
                ->description('All Time')
                ->color('primary')
                ->icon('heroicon-o-shopping-cart'),

            Stat::make('Total Harga Pembelian', 'Rp ' . number_format($totalhargaPembelian, 0, ',', '.'))
                ->description('All Time')
                ->color('primary')
                ->icon('heroicon-o-chart-bar'),

            Stat::make('Total Pengeluaran', 'Rp ' . number_format(($pengeluaran - $kembalian), 0, ',', '.'))
                ->description('All Time')
                ->color('success')
                ->icon('heroicon-o-currency-dollar'),

            Stat::make('Total Utang', 'Rp ' . number_format($utang, 0, ',', '.'))
                ->description('All Time')
                ->color('danger')
                ->icon('heroicon-o-exclamation-circle'),
        ];
    }
}
