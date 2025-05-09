<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\Stok;
use App\Models\Produk;
use Carbon\Carbon;

class StokProdukSeeder extends Seeder
{
    /**
     * Jalankan seeder.
     */
    public function run(): void
    {
        $faker = Faker::create();
        // Ambil semua produk yang sudah ada
        $produkList = Produk::all();
        foreach ($produkList as $produk) {
            // Menambahkan stok awal (jenis "In")
            $stokAwal = 0; // Stok awal 0
            $this->addStok($produk, $stokAwal, null, 'Stok Awal', Carbon::now()->subDays(30));

            // Menambahkan stok pembelian awal untuk memastikan ada stok
            $stokPembelianAwal = $faker->numberBetween(10, 100);
            $this->addStok($produk, $stokPembelianAwal, 'In', 'Pembelian', Carbon::now()->subDays(28));

            // Menambahkan riwayat stok
            $jumlahRiwayat = rand(2, 5); // Minimal 2 dan maksimal 5 riwayat stok
            for ($i = 0; $i < $jumlahRiwayat; $i++) {
                // 40% kemungkinan "In" (termasuk "Pembelian"), 60% kemungkinan "Out"
                $jenisStok = $faker->randomElement(['In', 'In', 'Out', 'Out', 'Out']);

                // Hitung stok tersedia saat ini
                $stokMasuk = Stok::where('id_produk', $produk->id_produk)
                    ->where('jenis_stok', 'In')
                    ->sum('jumlah_stok');
                $stokKeluar = Stok::where('id_produk', $produk->id_produk)
                    ->where('jenis_stok', 'Out')
                    ->sum('jumlah_stok');
                $stokTersedia = $stokMasuk - $stokKeluar;

                // Jika jenis stok adalah "Out", pastikan jumlah yang dikeluarkan tidak melebihi stok tersedia
                if ($jenisStok === 'Out') {
                    if ($stokTersedia <= 0) {
                        // Jika stok habis, ubah jenis menjadi "In" dengan transaksi "Pembelian"
                        $jenisStok = 'In';
                        $jumlahStok = $faker->numberBetween(5, 30);
                        $jenisTransaksi = 'Pembelian';
                    } else {
                        // Jika ada stok, pastikan jumlah yang dikeluarkan tidak melebihi stok tersedia
                        $jumlahStok = $faker->numberBetween(1, min($stokTersedia, 20));
                        $jenisTransaksi = 'INV-' . Carbon::now()->format('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                        $keterangan = 'Penjualan Produk'; // Keterangan otomatis untuk invoice
                    }
                } else {
                    // Jenis stok adalah "In"
                    $jumlahStok = $faker->numberBetween(5, 30);

                    // 50% kemungkinan transaksi Pembelian, 50% Manual untuk stok In
                    $jenisTransaksiOptions = ['Manual', 'Pembelian'];
                    $jenisTransaksi = $faker->randomElement($jenisTransaksiOptions);
                }

                // Generate tanggal acak antara stok awal dan sekarang
                $tanggalStok = Carbon::now()
                    ->subDays(rand(1, 27))
                    ->setTime(rand(0, 23), rand(0, 59), rand(0, 59));
                $this->addStok($produk, $jumlahStok, $jenisStok, $jenisTransaksi, $tanggalStok);
            }
        }
    }

    /**
     * Menambahkan riwayat stok ke tabel Stok.
     *
     * @param  Produk  $produk
     * @param  int     $jumlahStok
     * @param  string|null  $jenisStok
     * @param  string|null  $jenisTransaksi
     * @param  string  $keterangan
     * @param  Carbon  $tanggalStok
     * @return void
     */
    private function addStok($produk, $jumlahStok, $jenisStok, $jenisTransaksi, $tanggalStok = null)
    {
        // Simpan jumlah stok selalu sebagai nilai positif
        Stok::create([
            'id_produk'       => $produk->id_produk,
            'jumlah_stok'     => $jumlahStok, // Selalu positif
            'jenis_stok'      => $jenisStok,
            'jenis_transaksi' => $jenisTransaksi,
            'tanggal_stok'    => $tanggalStok ?? Carbon::now(),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    /**
     * Menghasilkan keterangan yang realistis.
     *
     * @param  string  $jenisStok
     * @return string
     */
}
