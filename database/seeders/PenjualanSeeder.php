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

        // Data contoh dengan format ID transaksi string
        $penjualanData = [
            [
                'id_penjualan' => 'INV-2025032801',
                'id_kasir' => 1,
                'id_pelanggan' => 1,
                'tanggal_penjualan' => Carbon::now(),
                'total_harga' => 50000,
                'status_penjualan' => 'Lunas',
            ],
            [
                'id_penjualan' => 'INV-2025032802',
                'id_kasir' => 2,
                'id_pelanggan' => 2,
                'tanggal_penjualan' => Carbon::now(),
                'total_harga' => 75000,
                'status_penjualan' => 'Belum Lunas',
            ]
        ];

        DB::table('penjualan')->insert($penjualanData);

        // Data penjualan_detail menyesuaikan dengan ID penjualan yang berbentuk string
        $penjualanDetailData = [
            [
                'id_penjualan' => 'INV-2025032801',
                'id_produk' => 1,
                'jumlah_produk' => 2,
            ],
            [
                'id_penjualan' => 'INV-2025032802',
                'id_produk' => 2,
                'jumlah_produk' => 1,
            ]
        ];

        DB::table('penjualan_detail')->insert($penjualanDetailData);

        // Data pembayaran juga harus sesuai dengan ID string
        $pembayaranData = [
            [
                'id_penjualan' => 'INV-2025032801',
                'tanggal_pembayaran' => Carbon::now(),
                'total_bayar' => 50000,
                'keterangan' => 'Dibayar lunas',
            ]
        ];

        DB::table('pembayaran')->insert($pembayaranData);
    }
}
