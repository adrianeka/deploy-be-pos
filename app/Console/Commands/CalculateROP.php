<?php

namespace App\Console\Commands;

use App\ReorderPointService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CalculateROP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculate-r-o-p';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hitung Safety Stock dan Reorder Point setiap awal bulan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $result = (new \App\ReorderPointService)->calculate();
            $bulanProses = now()->translatedFormat('F Y');
            $jumlahProduk = isset($result['data']) ? count($result['data']) : 0;
            $this->info("Perhitungan ROP selesai untuk bulan: $bulanProses");
            $this->info("Jumlah produk yang diproses: $jumlahProduk");
            Log::info("ROP dijalankan pada: " . now() . " | Bulan: $bulanProses | Jumlah produk: $jumlahProduk");
        } catch (\Throwable $e) {
            $this->error("Terjadi error: " . $e->getMessage());
            Log::error("Error saat menjalankan ROP: " . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }
}
