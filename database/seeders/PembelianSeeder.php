<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PembelianSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();

        // Truncate tabel untuk menghindari duplikasi
        DB::table('pembayaran')->truncate();
        DB::table('pembelian_detail')->truncate();
        DB::table('pembelian')->truncate();

        Schema::enableForeignKeyConstraints();

        // Data contoh dengan format ID transaksi string
        $pembelianData = [
            [
                'id_pembelian' => 1,
                'id_pemasok' => 1,
                'tanggal_pembelian' => Carbon::now(),
                'total_harga' => 50000,
                'status_pembelian' => 'Lunas',
            ],
            [
                'id_pembelian' => 2,
                'id_pemasok' => 2,
                'tanggal_pembelian' => Carbon::now(),
                'total_harga' => 75000,
                'status_pembelian' => 'Belum Lunas',
            ]
        ];

        DB::table('pembelian')->insert($pembelianData);

        // Data pembelian_detail menyesuaikan dengan ID pembelian yang berbentuk string
        $pembelianDetailData = [
            [
                'id_pembelian' => 1,
                'id_produk' => 1,
                'jumlah_produk' => 2,
            ],
            [
                'id_pembelian' => 1,
                'id_produk' => 2,
                'jumlah_produk' => 1,
            ]
        ];

        DB::table('pembelian_detail')->insert($pembelianDetailData);

        // Data pembayaran juga harus sesuai dengan ID string
        $pembayaranData = [
            [
                'id_penjualan' => null,
                'id_pembelian' => 1,
                'tanggal_pembayaran' => Carbon::now(),
                'total_bayar' => 50000,
                'keterangan' => 'Lunas',
            ]
        ];

        DB::table('pembayaran')->insert($pembayaranData);
    }
}
