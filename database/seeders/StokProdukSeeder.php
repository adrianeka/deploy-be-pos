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
            Stok::create([
                'id_produk'        => $produk->id_produk,
                'jumlah_stok'      => $faker->numberBetween(10, 100), // Stok antara 10 - 100
                'jenis_stok'       => 'In',
                'jenis_transaksi'  => 'Penambahan Awal',
                'keterangan'       => 'Stok awal ditambahkan melalui seeder',
                'tanggal_stok'     => Carbon::now(),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }
}
