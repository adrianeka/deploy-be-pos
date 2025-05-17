<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\Stok;
use App\Models\Produk;

class StokProdukSeeder extends Seeder
{
    /**
     * Jalankan seeder.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Ambil semua produk
        $produkList = Produk::all();

        foreach ($produkList as $produk) {
            // Tambahkan stok awal 0
            $this->addStok($produk, 0, null, null, 'Stok Awal');

            // Tambahkan pembelian awal agar stok tidak kosong
            $stokPembelianAwal = $faker->numberBetween(10, 100);
            $this->addStok($produk, $stokPembelianAwal, 'in', 'Pembelian', 'Pembelian Produk');

            // Tambahkan riwayat stok acak
            $jumlahRiwayat = rand(2, 5);

            for ($i = 0; $i < $jumlahRiwayat; $i++) {
                $jenisStok = $faker->randomElement(['in', 'in', 'out', 'out', 'out']);

                // Hitung stok tersedia
                $stokMasuk = Stok::where('id_produk', $produk->id_produk)
                    ->where('jenis_stok', 'in')
                    ->sum('jumlah_stok');
                $stokKeluar = Stok::where('id_produk', $produk->id_produk)
                    ->where('jenis_stok', 'out')
                    ->sum('jumlah_stok');
                $stokTersedia = $stokMasuk - $stokKeluar;

                // Jika stok out, pastikan tidak melebihi stok tersedia
                if ($jenisStok === 'out') {
                    if ($stokTersedia <= 0) {
                        // Jika stok habis, ubah ke in
                        $jenisStok = 'in';
                        $jumlahStok = $faker->numberBetween(5, 30);
                        $jenisTransaksi = 'Pembelian';
                    } else {
                        $jumlahStok = $faker->numberBetween(1, min($stokTersedia, 20));
                        $jenisTransaksi = 'INV-' . now()->format('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                        $keterangan = 'Penjualan Produk';
                    }
                } else {
                    // Untuk stok in
                    $jumlahStok = $faker->numberBetween(5, 30);
                    $jenisTransaksi = $faker->randomElement(['Manual', 'Pembelian']);
                    $keterangan = $jenisTransaksi === 'Pembelian' ? 'Pembelian Produk' : $this->generateKeterangan('in');
                }

                $this->addStok($produk, $jumlahStok, $jenisStok, $jenisTransaksi, $keterangan);
            }
        }
    }

    /**
     * Tambahkan stok baru.
     */
    private function addStok($produk, $jumlahStok, $jenisStok, $jenisTransaksi, $keterangan)
    {
        Stok::create([
            'id_produk'       => $produk->id_produk,
            'jumlah_stok'     => $jumlahStok,
            'jenis_stok'      => $jenisStok,
            'jenis_transaksi' => $jenisTransaksi,
            'keterangan'      => $keterangan,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    /**
     * Keterangan stok otomatis.
     */
    private function generateKeterangan($jenisStok)
    {
        $keteranganList = [
            'in' => ['Stok masuk manual', 'Restok dari gudang'],
            'out' => ['Kadaluarsa', 'Retur pelanggan', 'Rusak'],
        ];
        return $keteranganList[$jenisStok][array_rand($keteranganList[$jenisStok])];
    }
}
