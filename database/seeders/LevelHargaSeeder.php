<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LevelHarga;
use App\Models\Produk;

class LevelHargaSeeder extends Seeder
{
    /**
     * Jalankan seeder.
     */
    public function run(): void
    {
        // Ambil semua produk yang sudah ada
        $produkList = Produk::all();

        foreach ($produkList as $produk) {
            // Level harga wajib: Standar
            $hargaStandar = $produk->harga_beli + rand(10, 15) * 1000; // Harga jual sedikit lebih tinggi dari harga beli
            $hargaStandar = round($hargaStandar, -3); // Menyesuaikan agar 3 angka terakhir menjadi 0
            LevelHarga::create([
                'id_produk'  => $produk->id_produk,
                'nama_level' => 'Standart',
                'harga_jual' => $hargaStandar,
            ]);

            // Level harga "Mahal" atau "Murah" berdasarkan harga beli
            if ($produk->harga_beli > 100000) {
                $hargaMahal = $produk->harga_beli + rand(15, 30) * 1000;
                $hargaMahal = round($hargaMahal, -3); // Menyesuaikan agar 3 angka terakhir menjadi 0
                LevelHarga::create([
                    'id_produk'  => $produk->id_produk,
                    'nama_level' => 'Mahal',
                    'harga_jual' => $hargaMahal,
                ]);
            } elseif ($produk->harga_beli < 50000) {
                $hargaMurah = $produk->harga_beli + rand(3, 10) * 1000;
                $hargaMurah = round($hargaMurah, -3); // Menyesuaikan agar 3 angka terakhir menjadi 0
                LevelHarga::create([
                    'id_produk'  => $produk->id_produk,
                    'nama_level' => 'Murah',
                    'harga_jual' => $hargaMurah,
                ]);
            }
        }
    }
}
