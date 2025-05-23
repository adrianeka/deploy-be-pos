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

        foreach ($produkList as $produk) {
            foreach (['this', 'last'] as $periode) {
                // Tentukan jumlah pembelian untuk bulan ini (2-4)
                $jumlahPembelianPerBulan = rand(2, 4);
                
                // Set rentang waktu bulan ini atau bulan lalu
                if ($periode === 'this') {
                    $start = Carbon::now()->startOfMonth();
                    $end = Carbon::now()->endOfMonth();
                } else {
                    $start = Carbon::now()->subMonth()->startOfMonth();
                    $end = Carbon::now()->subMonth()->endOfMonth();
                }
                
                // Bagi bulan menjadi segment untuk distribusi pembelian lebih merata
                $totalDays = $end->diffInDays($start);
                $segmentSize = $totalDays / $jumlahPembelianPerBulan;
                
                // Buat 2-4 pembelian per bulan
                for ($i = 0; $i < $jumlahPembelianPerBulan; $i++) {
                    // Distribusikan tanggal pembelian secara merata dalam bulan
                    $segmentStart = $start->clone()->addDays($i * $segmentSize);
                    $segmentEnd = $start->clone()->addDays(($i + 1) * $segmentSize - 1);
                    
                    if ($segmentEnd->greaterThan($end)) {
                        $segmentEnd = $end->clone();
                    }
                    
                    $createdAt = Carbon::createFromTimestamp(
                        rand($segmentStart->timestamp, $segmentEnd->timestamp)
                    );
                    
                    // Lead time pembelian 1-3 hari
                    $updatedAt = (clone $createdAt)->addDays(rand(1, 3));
                    
                    $idPemasok = rand(1, 2); // Sesuaikan dengan pemasok yang ada
                    $status = (rand(0, 1) == 1) ? 'Lunas' : 'Belum Lunas';
                    
                    $idPembelian = DB::table('pembelian')->insertGetId([
                        'id_pemasok' => $idPemasok,
                        'total_harga' => 0,
                        'status_pembelian' => $status,
                        'created_at' => $createdAt,
                        'updated_at' => $updatedAt,
                    ]);
                    
                    // Tambahkan produk ini ke pembelian
                    $jumlahProduk = rand(5, 20); // Jumlah produk lebih masuk akal
                    DB::table('pembelian_detail')->insert([
                        'id_pembelian' => $idPembelian,
                        'id_produk' => $produk->id_produk,
                        'jumlah_produk' => $jumlahProduk,
                        'created_at' => $createdAt,
                        'updated_at' => $updatedAt,
                    ]);
                    
                    // Tambahkan record ke tabel stok
                    DB::table('stok')->insert([
                        'id_produk' => $produk->id_produk,
                        'jumlah_stok' => $jumlahProduk,
                        'jenis_stok' => 'In',
                        'jenis_transaksi' => 'Pembelian',
                        'keterangan' => 'Pembelian Produk',
                        'created_at' => $updatedAt,
                        'updated_at' => $updatedAt
                    ]);
                    
                    // Hitung total harga pembelian
                    $totalHarga = DB::table('pembelian_detail')
                        ->join('produk', 'pembelian_detail.id_produk', '=', 'produk.id_produk')
                        ->where('pembelian_detail.id_pembelian', $idPembelian)
                        ->sum(DB::raw('produk.harga_beli * pembelian_detail.jumlah_produk'));
                    
                    DB::table('pembelian')->where('id_pembelian', $idPembelian)->update([
                        'total_harga' => $totalHarga,
                    ]);
                    
                    // Tambahkan pembayaran
                    $jenisPembayaran = ($status === 'Lunas') ? 'tunai' : 'transfer';
                    
                    // Fix calculation - gunakan rand(100, 120)/100 untuk menghasilkan 1.0 sampai 1.2
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
                        'id_pembelian' => $idPembelian,
                        'created_at' => $createdAt,
                        'updated_at' => $updatedAt,
                    ]);
                }
            }
        }
    }
}