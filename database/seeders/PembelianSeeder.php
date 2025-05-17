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
        DB::table('pembayaran')->truncate();

        Schema::enableForeignKeyConstraints();

        $now = Carbon::now();

        // Insert data pembelian
        $pembelianData = [
            [
                'id_pembelian' => 1,
                'id_pemasok' => 1,
                'total_harga' => 0,
                'status_pembelian' => 'Lunas',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_pembelian' => 2,
                'id_pemasok' => 2,
                'total_harga' => 0,
                'status_pembelian' => 'Belum Lunas',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        DB::table('pembelian')->insert($pembelianData);

        // Insert detail pembelian
        $pembelianDetailData = [
            ['id_pembelian' => 1, 'id_produk' => 1, 'jumlah_produk' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id_pembelian' => 1, 'id_produk' => 2, 'jumlah_produk' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id_pembelian' => 2, 'id_produk' => 1, 'jumlah_produk' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id_pembelian' => 2, 'id_produk' => 2, 'jumlah_produk' => 2, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('pembelian_detail')->insert($pembelianDetailData);

        // Hitung ulang total harga pembelian
        $pembelianIds = collect($pembelianData)->pluck('id_pembelian');

        foreach ($pembelianIds as $idPembelian) {
            $totalHarga = DB::table('pembelian_detail')
                ->join('produk', 'pembelian_detail.id_produk', '=', 'produk.id_produk')
                ->where('pembelian_detail.id_pembelian', $idPembelian)
                ->sum(DB::raw('produk.harga_beli * pembelian_detail.jumlah_produk'));

            DB::table('pembelian')->where('id_pembelian', $idPembelian)->update([
                'total_harga' => $totalHarga,
            ]);
        }

        // Insert pembayaran pembelian (1 tunai, 1 transfer)
        $pembayaranData = [
            [
                'id_pembayaran' => 4,
                'id_tipe_transfer' => null, // Tunai
                'jenis_pembayaran' => 'tunai',
                'total_bayar' => 323000,
                'keterangan' => 'Lunas tunai',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_pembayaran' => 5,
                'id_tipe_transfer' => 1, // Misal: BCA
                'jenis_pembayaran' => 'transfer',
                'total_bayar' => 100000,
                'keterangan' => 'Bayar sebagian via transfer',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('pembayaran')->insert($pembayaranData);

        // Pembayaran-pembelian relasi
        $pembayaranPembelianData = [
            [
                'id_pembayaran' => 4,
                'id_pembelian' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_pembayaran' => 5,
                'id_pembelian' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        DB::table('pembayaran_pembelian')->insert($pembayaranPembelianData);
    }
}
