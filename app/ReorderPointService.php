<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Produk;

class ReorderPointService
{
    public function calculate()
    {
        $produkList = Produk::all();
        $updated = [];

        foreach ($produkList as $produk) {
            // 1. Generate 12 bulan terakhir (dari 13 bulan lalu sampai 1 bulan lalu)
            $period = CarbonPeriod::create(now()->subMonths(12)->startOfMonth(), '1 month', now()->subMonth()->startOfMonth());
            $months = [];
            foreach ($period as $date) {
                $months[$date->format('Y-m')] = 0;
            }

            // 2. Ambil data penjualan per bulan yang ada
            $salesRaw = DB::table('penjualan_detail')
                ->join('penjualan', 'penjualan_detail.id_penjualan', '=', 'penjualan.id_penjualan')
                ->where('penjualan_detail.id_produk', $produk->id_produk)
                ->whereBetween('penjualan.tanggal_penjualan', [
                    now()->subMonths(13)->startOfMonth(),
                    now()->subMonth()->endOfMonth(),
                ])
                ->selectRaw('DATE_FORMAT(penjualan.tanggal_penjualan, "%Y-%m") as bulan, SUM(jumlah_produk) as total')
                ->groupBy('bulan')
                ->pluck('total', 'bulan')
                ->toArray();

            // 3. Gabungkan data penjualan ke bulan-bulan yang sudah disiapkan
            $salesData = array_merge($months, $salesRaw);

            if (count($salesData) < 1) continue;

            // 4. Hitung statistik
            $values = array_values($salesData);
            $max = max($values);
            $avg = array_sum($values) / count($values);
            $leadTime = 4;
            $safetyStock = ($max - $avg) * $leadTime;

            // 5. Ambil data penjualan bulan lalu
            $lastMonth = Carbon::now()->subMonth();
            $daysInMonth = $lastMonth->daysInMonth;

            $lastMonthSales = DB::table('penjualan_detail')
                ->join('penjualan', 'penjualan_detail.id_penjualan', '=', 'penjualan.id_penjualan')
                ->where('penjualan_detail.id_produk', $produk->id_produk)
                ->whereMonth('penjualan.tanggal_penjualan', $lastMonth->month)
                ->whereYear('penjualan.tanggal_penjualan', $lastMonth->year)
                ->sum('jumlah_produk');

            $dailyDemand = $daysInMonth > 0 ? $lastMonthSales / $daysInMonth : 0;

            $reorderPoint = ($dailyDemand * $leadTime) + $safetyStock;

            // 6. Simpan hasil
            $produk->update([
                'stok_minimum' => round($safetyStock),
            ]);

            $updated[] = [
                'sales' => $values,
                'avg' => array_sum($values) . '/' . count($values),
                'rata-rata perbulan' => $avg,
                'id_produk' => $produk->id_produk,
                'nama_produk' => $produk->nama_produk ?? '-',
                'max' => $max,
                'lastMonthSales' => $lastMonthSales,
                'dailyDemand' => $lastMonthSales . '/' . $daysInMonth,
                'RATA RATA' => $dailyDemand,
                'safety_stock' => "($max - $avg) * $leadTime",
                'SS' => $safetyStock,
                'reorder_point' => "($dailyDemand * $leadTime) + $safetyStock",
                'STOK MINIMUM' => $reorderPoint,
            ];
        }

        return response()->json([
            'success' => true,
            'leadtime' => $leadTime,
            'lastMonth' => $lastMonth,
            'daysInMonth' => $daysInMonth,
            'data' => $updated,
        ]);
    }
}
