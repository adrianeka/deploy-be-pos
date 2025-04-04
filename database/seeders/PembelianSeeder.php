<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PembelianSeeder extends Seeder
{
    public function run()
    {
        DB::table('pembelian')->insert([
            [
                'id_pemilik' => 1,
                'id_pemasok' => 1,
                'tanggal_pembelian' => Carbon::now()->subDays(5),
                'total_harga' => 500000,
                'status_pembelian' => 'diproses',
            ],
            [
                'id_pemilik' => 2,
                'id_pemasok' => 2,
                'tanggal_pembelian' => Carbon::now()->subDays(10),
                'total_harga' => 750000,
                'status_pembelian' => 'diproses',
            ],
        ]);
    }
}
