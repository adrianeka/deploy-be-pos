<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Produk;
use ArPHP\I18N\Arabic;
use Illuminate\Support\Facades\Log;
use Exception;

class ReorderPointService
{
    protected function isMusiman($carbonDate)
    {
        try {
            $arabic = new Arabic();
            $timestamp = $carbonDate->timestamp;
            $correction = $arabic->dateCorrection($timestamp);
            $hijriDate = $arabic->date('Y m d', $timestamp, $correction);
            list($hijriYear, $hijriMonth, $hijriDay) = explode(' ', $hijriDate);
            return in_array((int)$hijriMonth, [9, 12]); // 9 = Ramadhan, 12 = Dzulhijjah
        } catch (Exception $e) {
            Log::error('Error checking musiman: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cari rentang tanggal masehi untuk bulan hijriah tertentu pada tahun masehi tertentu
     */
    protected function getHijriMonthRange($targetHijriMonth, $year)
    {
        try {
            $arabic = new Arabic();
            $start = null;
            $end = null;
            
            // Cari tanggal 1-31 di setiap bulan masehi tahun itu
            for ($month = 1; $month <= 12; $month++) {
                for ($day = 1; $day <= 31; $day++) {
                    if (!checkdate($month, $day, $year)) continue;
                    
                    $timestamp = mktime(0, 0, 0, $month, $day, $year);
                    $correction = $arabic->dateCorrection($timestamp);
                    $hijriDate = $arabic->date('Y m d', $timestamp, $correction);
                    list($hijriYear, $hijriMonth, $hijriDay) = explode(' ', $hijriDate);
                    
                    if ((int)$hijriMonth === $targetHijriMonth) {
                        $start = $start ?? date('Y-m', $timestamp);
                        $end = date('Y-m', $timestamp);
                    } elseif ($start !== null && (int)$hijriMonth !== $targetHijriMonth) {
                        // Sudah lewat bulan target, stop
                        break 2;
                    }
                }
            }
            return [$start, $end];
        } catch (Exception $e) {
            Log::error('Error getting Hijri month range: ' . $e->getMessage());
            return [null, null];
        }
    }

    /**
     * Ambil data penjualan per bulan untuk produk
     */
    protected function getSalesData($produkId)
    {
        try {
            // 1. Buat array penjualan per bulan
            $period = CarbonPeriod::create(
                now()->subYear()->startOfMonth(),
                '1 month',
                now()->subMonth()->startOfMonth()
            );
            $months = [];
            foreach ($period as $date) {
                $months[$date->format('Y-m')] = 0;
            }

            // 2. Ambil data penjualan per bulan yang ada
            $salesRaw = DB::table('penjualan_detail')
                ->join('penjualan', 'penjualan_detail.id_penjualan', '=', 'penjualan.id_penjualan')
                ->where('penjualan_detail.id_produk', $produkId)
                ->whereBetween('penjualan.created_at', [
                    now()->subMonths(12)->startOfMonth(),
                    now()->subMonth()->endOfMonth(),
                ])
                ->selectRaw('DATE_FORMAT(penjualan.created_at, "%Y-%m") as bulan, SUM(jumlah_produk) as total')
                ->groupBy('bulan')
                ->pluck('total', 'bulan')
                ->toArray();

            // 3. Gabungkan data penjualan ke bulan-bulan yang sudah disiapkan
            return array_merge($months, $salesRaw);
        } catch (Exception $e) {
            Log::error('Error getting sales data: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Hitung lead time dari pembelian bulan lalu
     */
    protected function calculateLeadTime($produkId, $lastMonth)
    {
        Log::info("Hitung lead time untuk produk: $produkId, bulan: $lastMonth");
        try {
            $pembelianBulanLalu = DB::table('pembelian_detail')
                ->join('pembelian', 'pembelian_detail.id_pembelian', '=', 'pembelian.id_pembelian')
                ->where('pembelian_detail.id_produk', $produkId)
                ->whereMonth('pembelian.created_at', $lastMonth->month)
                ->whereYear('pembelian.created_at', $lastMonth->year)
                ->select('pembelian.created_at', 'pembelian.updated_at')
                ->get();

            $leadTimes = [];
            foreach ($pembelianBulanLalu as $row) {
                if ($row->created_at && $row->updated_at) {
                    $leadTimes[] = Carbon::parse($row->created_at)->diffInDays(Carbon::parse($row->updated_at));
                }
            }
            
            Log::info("Lead times: ", $leadTimes);
            Log:info("Dibagi : " . count($leadTimes));
            // Ambil rata-rata lead time atau default ke 2, bulatkan minimum 1 hari
            $leadTime = count($leadTimes) > 0 ? array_sum($leadTimes) / count($leadTimes) : 2;
            Log::info("Lead time: " . $leadTime);
            return max(1, round($leadTime));
        } catch (Exception $e) {
            Log::error('Error calculating lead time: ' . $e->getMessage());
            return 2; // Default lead time jika error
        }
    }

    public function calculate()
    {
        try {
            // Inisialisasi tanggal dan variabel
            $now = Carbon::now()->setTimezone('Asia/Jakarta');
            $lastDayThisMonth = Carbon::now()->endOfMonth()->setTimezone('Asia/Jakarta');
            $lastMonth = Carbon::now()->subMonth()->setTimezone('Asia/Jakarta');
            
            // Cek musiman (Ramadhan/Dzulhijjah)
            $isMusiman = $this->isMusiman($now) || $this->isMusiman($lastDayThisMonth);
            $updated = [];
            
            // Proses tiap produk
            foreach (Produk::all() as $produk) {
                try {
                    // Ambil data penjualan dan cek apakah kosong
                    $salesData = $this->getSalesData($produk->id_produk);
                    if (empty($salesData)) continue;
                    
                    // Hitung statistik penjualan
                    $values = array_values($salesData);
                    $nonZeroValues = array_filter($values, fn($v) => $v > 0);
                    $max = $nonZeroValues ? max($nonZeroValues) : 0;
                    $avg = array_sum($values) / count($values);
                    
                    // Hitung lead time
                    $leadTime = $this->calculateLeadTime($produk->id_produk, $lastMonth);
                    
                    // Hitung safety stock
                    $safetyStock = count($nonZeroValues) > 1 ? ($max - $avg) * $leadTime : $avg;
                    
                    // Ambil penjualan periode terakhir (musiman atau bulan lalu)
                    $lastPeriodSales = 0;
                    $daysInPeriod = $lastMonth->daysInMonth;
                    
                    if ($isMusiman) {
                        try {
                            // Deteksi bulan hijriah dan ambil penjualan tahun lalu bulan yang sama
                            $arabic = new Arabic();
                            $timestamp = $now->timestamp;
                            $correction = $arabic->dateCorrection($timestamp);
                            $hijriDate = $arabic->date('Y m d', $timestamp, $correction);
                            list($hijriYear, $hijriMonth, $hijriDay) = explode(' ', $hijriDate);
                            
                            // Cari range bulan hijriah yang sama tahun lalu
                            [$start, $end] = $this->getHijriMonthRange((int)$hijriMonth, $now->year - 1);
                            
                            if ($start && isset($salesData[$start])) {
                                $lastPeriodSales = $salesData[$start];
                                $daysInPeriod = Carbon::parse($start)->daysInMonth;
                                Log::info("Last Period Sales (Musiman): $lastPeriodSales, Days: $daysInPeriod");
                            }
                        } catch (Exception $e) {
                            Log::error('Error processing musiman data: ' . $e->getMessage());
                        }
                    }
                    
                    // Fallback ke bulan lalu jika tidak ada data musiman
                    if ($lastPeriodSales < 1) {
                        $lastPeriodSales = $salesData[$lastMonth->format('Y-m')] ?? 0;
                        $daysInPeriod = $lastMonth->daysInMonth;
                    }
                    
                    // Jika tidak ada penjualan periode terakhir, skip perhitungan ROP
                    if ($lastPeriodSales < 1) {
                        $updated[] = [
                            'id_produk' => $produk->id_produk,
                            'nama_produk' => $produk->nama_produk ?? '-',
                            'note' => 'Penjualan periode sebelumnya 0, tidak ada perhitungan ROP',
                            'stok_minimum' => $produk->stok_minimum
                        ];
                        continue;
                    }
                    
                    // Hitung ROP
                    $dailyDemand = $lastPeriodSales / max(1, $daysInPeriod);
                    $reorderPoint = ($dailyDemand * $leadTime) + $safetyStock;
                    
                    // Update produk
                    $produk->update(['stok_minimum' => round($reorderPoint)]);
                    
                    // Tambahkan hasil ke array updated
                    $updated[] = [
                        'salesData' => $salesData,
                        'leadtime' => $leadTime,
                        'id_produk' => $produk->id_produk,
                        'nama_produk' => $produk->nama_produk ?? '-',
                        'max' => $max,
                        'lastPeriodSales' => $lastPeriodSales,
                        'dailyDemand' => "$lastPeriodSales / $daysInPeriod",
                        'RATA RATA' => $dailyDemand,
                        'SS' => $safetyStock,
                        'STOK MINIMUM' => $reorderPoint,
                        'isMusiman' => $isMusiman,
                    ];
                    
                } catch (Exception $e) {
                    Log::error("Error processing product {$produk->id_produk}: " . $e->getMessage());
                }
            }
            
            return [
                'success' => true,
                'isMusiman' => $isMusiman,
                'data' => $updated,
            ];
            
        } catch (Exception $e) {
            Log::error('Critical error in calculate(): ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}