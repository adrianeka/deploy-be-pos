<?php

namespace Database\Seeders;

use App\Models\Pembayaran;
use App\Models\PembayaranPembelian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PembelianSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();

        DB::table('pembelian_detail')->truncate();
        DB::table('pembelian')->truncate();
        DB::table('pembayaran_pembelian')->truncate();

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
        $pembayaranData = Pembayaran::create([
                'tanggal_pembayaran' => Carbon::now(),
                'total_bayar' => 50000,
                'id_metode_pembayaran' => 1,
                'keterangan' => 'Lunas',
        ]);

        PembayaranPembelian::create([
                'id_pembayaran' => $pembayaranData->id_pembayaran,
                'id_pembelian' => 1,
        ]);
    }
}
