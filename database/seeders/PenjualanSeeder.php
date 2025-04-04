<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PenjualanSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();

        // Truncate tabel untuk menghindari duplikasi
        DB::table('pembayaran')->truncate();
        DB::table('penjualan_detail')->truncate();
        DB::table('penjualan')->truncate();

        Schema::enableForeignKeyConstraints();

        $penjualanData = [];
        $penjualanDetailData = [];
        $pembayaranData = [];

        for ($i = 1; $i <= 20; $i++) {
            $id_penjualan = sprintf('INV-20250328%02d', $i);
            
            $penjualanData[] = [
                'id_penjualan' => $id_penjualan,
                'id_kasir' => rand(1, 2), // Kasir acak
                'id_pelanggan' => 1, // Semua transaksi milik pelanggan 1
                'tanggal_penjualan' => Carbon::now(),
                'total_harga' => rand(30000, 100000),
                'status_penjualan' => $i % 2 == 0 ? 'Lunas' : 'Belum Lunas',
            ];

            $penjualanDetailData[] = [
                'id_penjualan' => $id_penjualan,
                'id_produk' => rand(1, 5), // Produk acak
                'jumlah_produk' => rand(1, 3),
            ];

            if ($i % 2 == 0) { // Jika transaksi lunas, tambahkan pembayaran
                $pembayaranData[] = [
                    'id_penjualan' => $id_penjualan,
                    'tanggal_pembayaran' => Carbon::now(),
                    'total_bayar' => rand(30000, 100000),
                    'keterangan' => 'Dibayar lunas',
                ];
            }
        }

        DB::table('penjualan')->insert($penjualanData);
        DB::table('penjualan_detail')->insert($penjualanDetailData);
        DB::table('pembayaran')->insert($pembayaranData);
    }
}
