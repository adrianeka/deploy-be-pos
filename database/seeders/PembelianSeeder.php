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
        Schema::enableForeignKeyConstraints();

        $produkList = DB::table('produk')->get();
        $userId = 1; // Ganti sesuai kebutuhan (misal admin/toko yang melakukan pembelian)

        foreach ($produkList as $produk) {
            foreach (['this', 'last'] as $periode) {
                $jumlahPembelianPerBulan = rand(2, 4);

                $start = $periode === 'this' ? Carbon::now()->startOfMonth() : Carbon::now()->subMonth()->startOfMonth();
                $end = $periode === 'this' ? Carbon::now()->endOfMonth() : Carbon::now()->subMonth()->endOfMonth();

                $totalDays = $end->diffInDays($start);
                $segmentSize = $totalDays / $jumlahPembelianPerBulan;

                for ($i = 0; $i < $jumlahPembelianPerBulan; $i++) {
                    $segmentStart = $start->clone()->addDays($i * $segmentSize);
                    $segmentEnd = $start->clone()->addDays(($i + 1) * $segmentSize - 1);

                    if ($segmentEnd->greaterThan($end)) {
                        $segmentEnd = $end->clone();
                    }

                    $createdAt = Carbon::createFromTimestamp(
                        rand($segmentStart->timestamp, $segmentEnd->timestamp)
                    );
                    $updatedAt = (clone $createdAt)->addDays(rand(1, 3));

                    // Generate custom id_pembelian
                    $tanggal = $createdAt->format('Ymd');
                    $prefix = "INV-{$userId}{$tanggal}";

                    $latestId = DB::table('pembelian')
                        ->whereDate('created_at', $createdAt->toDateString())
                        ->where('id_pembelian', 'like', "{$prefix}%")
                        ->orderByDesc('id_pembelian')
                        ->value('id_pembelian');

                    $lastNumber = $latestId ? (int)substr($latestId, -3) : 0;
                    $urutan = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
                    $idPembelianStr = "{$prefix}{$urutan}";

                    $idPemasok = rand(1, 2);
                    $status = (rand(0, 1) == 1) ? 'Lunas' : 'Belum Lunas';

                    DB::table('pembelian')->insert([
                        'id_pembelian' => $idPembelianStr,
                        'id_pemasok' => $idPemasok,
                        'total_harga' => 0,
                        'status_pembelian' => $status,
                        'created_at' => $createdAt,
                        'tanggal_kedatangan' => $updatedAt,
                    ]);

                    $jumlahProduk = rand(5, 20);
                    DB::table('pembelian_detail')->insert([
                        'id_pembelian' => $idPembelianStr,
                        'id_produk' => $produk->id_produk,
                        'jumlah_produk' => $jumlahProduk,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);

                    DB::table('stok')->insert([
                        'id_produk' => $produk->id_produk,
                        'jumlah_stok' => $jumlahProduk,
                        'jenis_stok' => 'In',
                        'jenis_transaksi' => 'Pembelian',
                        'keterangan' => 'Pembelian Produk',
                        'created_at' => $updatedAt,
                        'updated_at' => $updatedAt
                    ]);

                    $totalHarga = DB::table('pembelian_detail')
                        ->join('produk', 'pembelian_detail.id_produk', '=', 'produk.id_produk')
                        ->where('pembelian_detail.id_pembelian', $idPembelianStr)
                        ->sum(DB::raw('produk.harga_beli * pembelian_detail.jumlah_produk'));

                    DB::table('pembelian')->where('id_pembelian', $idPembelianStr)->update([
                        'total_harga' => $totalHarga,
                    ]);

                    $jenisPembayaran = ($status === 'Lunas') ? 'tunai' : 'transfer';
                    $multiplier = ($status === 'Lunas') ? (rand(100, 120) / 100) : 0.5;
                    $totalBayar = intval($totalHarga * $multiplier);

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
                        'id_pembelian' => $idPembelianStr,
                        'created_at' => $createdAt,
                        'updated_at' => $updatedAt,
                    ]);
                }
            }
        }
    }
}