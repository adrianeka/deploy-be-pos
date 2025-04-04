<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PembelianDetailSeeder extends Seeder
{
    public function run()
    {
        DB::table('pembelian_detail')->insert([
            [
                'id_pembelian' => 1,
                'id_produk' => 1,
                'jumlah_produk' => 5,
            ],
            [
                'id_pembelian' => 1,
                'id_produk' => 2,
                'jumlah_produk' => 3,
            ],
            [
                'id_pembelian' => 2,
                'id_produk' => 3,
                'jumlah_produk' => 10,
            ],
        ]);
    }
}

