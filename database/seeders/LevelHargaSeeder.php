<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\LevelHarga;
use App\Models\Produk;

class LevelHargaSeeder extends Seeder
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
            // Level harga wajib: Standar
            LevelHarga::create([
                'id_produk'  => $produk->id_produk,
                'nama_level' => 'Standart',
                'harga_jual' => $produk->harga_beli + 5000, // Harga jual sedikit lebih tinggi dari harga beli
            ]);

            // Tambahkan level harga "Mahal" atau "Murah" berdasarkan harga beli
            if ($produk->harga_beli > 100000) {
                LevelHarga::create([
                    'id_produk'  => $produk->id_produk,
                    'nama_level' => 'Mahal',
                    'harga_jual' => $produk->harga_beli + 20000,
                ]);
            } elseif ($produk->harga_beli < 50000) {
                LevelHarga::create([
                    'id_produk'  => $produk->id_produk,
                    'nama_level' => 'Murah',
                    'harga_jual' => $produk->harga_beli + 10000,
                ]);
            }
        }
    }
}
