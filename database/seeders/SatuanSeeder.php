<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SatuanSeeder extends Seeder
{
    public function run()
    {
        DB::table('satuans')->insert([
            ['id_satuan' => 1, 'nama_satuan' => 'Pcs'],
            ['id_satuan' => 2, 'nama_satuan' => 'Kg'],
            ['id_satuan' => 3, 'nama_satuan' => 'Liter'],
            ['id_satuan' => 4, 'nama_satuan' => 'Dus'],
        ]);
    }
}
