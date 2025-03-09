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
        $produks = Produk::all(); // Ambil semua produk

        foreach ($produks as $produk) {
            $levels = ['Standard', 'Grosir', 'Reseller'];
            $harga_beli = $produk->harga_beli;

            $levelHargaData = [];
            foreach ($levels as $index => $level) {
                $harga_jual = match ($level) {
                    'Standard' => $harga_beli * 1.2, // 20% profit
                    'Grosir'   => $harga_beli * 1.15, // 15% profit
                    'Reseller' => $harga_beli * 1.1, // 10% profit
                    default    => $harga_beli * 1.2,
                };

                $levelHargaData[] = [
                    'id_produk' => $produk->id_produk,
                    'nama_level' => $level,
                    'harga_jual' => round($harga_jual, 2),
                    'is_applied' => $index === 0, // Standard sebagai default
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            LevelHarga::insert($levelHargaData);
        }
    }
}
