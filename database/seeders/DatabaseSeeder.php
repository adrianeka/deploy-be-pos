<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(UserSeeder::class);
        $this->call(KategoriSeeder::class);
        $this->call(SatuanSeeder::class);
        $this->call(PemasokSeeder::class);
        $this->call(PelangganSeeder::class);
        $this->call(PenerimaZakatSeeder::class);
        $this->call(ProdukSeeder::class);
        $this->call(LevelHargaSeeder::class);
        $this->call(StokProdukSeeder::class);
        $this->call(TipeTransferSeeder::class);
        $this->call(MetodePembayaranSeeder::class);
        $this->call(PenjualanSeeder::class);
        $this->call(PembelianSeeder::class);
        // $this->call(PembayaranSeeder::class);
        // $this->call(BayarZakatSeeder::class);
    }
}
