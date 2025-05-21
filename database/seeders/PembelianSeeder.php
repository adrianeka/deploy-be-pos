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
        DB::table('pembelian_detail')->truncate();
        DB::table('pembelian')->truncate();
        DB::table('pembayaran_pembelian')->truncate();
        // DB::table('pembayaran')->truncate(); // Jangan truncate pembayaran
        Schema::enableForeignKeyConstraints();

        for ($i = 1; $i <= 10; $i++) {
            $createdAt = Carbon::now()->subDays(rand(0, 30));
            $updatedAt = Carbon::now()->subDays(rand(0, 30));
            $idPemasok = ($i % 2) + 1;
            $status = ($i % 2 == 0) ? 'Lunas' : 'Belum Lunas';

            $idPembelian = DB::table('pembelian')->insertGetId([
                'id_pemasok' => $idPemasok,
                'total_harga' => 0,
                'status_pembelian' => $status,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);

            // Tambah 2 produk ke pembelian
            $detail = [];
            for ($j = 1; $j <= 2; $j++) {
                $detail[] = [
                    'id_pembelian' => $idPembelian,
                    'id_produk' => $j,
                    'jumlah_produk' => rand(1, 3),
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ];
            }
            DB::table('pembelian_detail')->insert($detail);

            // Hitung total harga pembelian
            $totalHarga = DB::table('pembelian_detail')
                ->join('produk', 'pembelian_detail.id_produk', '=', 'produk.id_produk')
                ->where('pembelian_detail.id_pembelian', $idPembelian)
                ->sum(DB::raw('produk.harga_beli * pembelian_detail.jumlah_produk'));

            DB::table('pembelian')->where('id_pembelian', $idPembelian)->update([
                'total_harga' => $totalHarga,
            ]);

            // Tambahkan pembayaran baru
            $jenisPembayaran = ($status === 'Lunas') ? 'tunai' : 'transfer';
            $totalBayar = ($status === 'Lunas') ? $totalHarga * 1.2 : intval($totalHarga * 0.5);

            $idPembayaran = DB::table('pembayaran')->insertGetId([
                'id_tipe_transfer' => ($jenisPembayaran === 'transfer') ? 1 : null,
                'jenis_pembayaran' => $jenisPembayaran,
                'total_bayar' => $totalBayar,
                'keterangan' => ($status === 'Lunas') ? 'Lunas tunai' : 'Bayar sebagian via transfer',
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);

            DB::table('pembayaran_pembelian')->insert([
                'id_pembayaran' => $idPembayaran,
                'id_pembelian' => $idPembelian,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);
        }
    }
}
