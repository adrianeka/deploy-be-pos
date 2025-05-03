<?php

namespace Database\Seeders;

use App\Models\MetodePembayaran;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MetodePembayaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MetodePembayaran::insert([
            ['jenis_pembayaran' => 'Tunai', 'id_tipe_transfer' => null],
            ['jenis_pembayaran' => 'Transfer', 'id_tipe_transfer' => 1], // BCA
            ['jenis_pembayaran' => 'Transfer', 'id_tipe_transfer' => 2], // Mandiri
            ['jenis_pembayaran' => 'Transfer', 'id_tipe_transfer' => 3], // BRI
            ['jenis_pembayaran' => 'Transfer', 'id_tipe_transfer' => 4], // OVO
            ['jenis_pembayaran' => 'Transfer', 'id_tipe_transfer' => 5], // DANA
            ['jenis_pembayaran' => 'Transfer', 'id_tipe_transfer' => 6], // SHOPEEPAY
            ['jenis_pembayaran' => 'Transfer', 'id_tipe_transfer' => 7], // GOPAY
        ]);
    }
}
