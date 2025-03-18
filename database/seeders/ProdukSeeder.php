<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\Produk;

class ProdukSeeder extends Seeder
{
    /**
     * Jalankan seeder.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $data = [];
        for ($i = 0; $i < 10; $i++) {
            $data[] = [
                'nama_produk' => $faker->word,
                'id_kategori' => rand(1, 2),
                'id_satuan' => rand(1, 4),
                'harga_beli' => $faker->numberBetween(10000, 500000),
                'id_pemilik' => rand(1, 2),
                'stok_minimum' => $faker->numberBetween(1, 100),
                'deskripsi' => $faker->sentence(10),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Produk::insert($data);
    }
}
