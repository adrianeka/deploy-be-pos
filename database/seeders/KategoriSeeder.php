<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    public function run()
    {
        DB::table('kategoris')->insert([
            ['id_kategori' => 1, 'nama_kategori' => 'Makanan', 'id_pemilik' => 1],
            ['id_kategori' => 2, 'nama_kategori' => 'Minuman', 'id_pemilik' => 1],
            ['id_kategori' => 3, 'nama_kategori' => 'Elektronik', 'id_pemilik' => 1],
            ['id_kategori' => 4, 'nama_kategori' => 'Baju', 'id_pemilik' => 2],
            ['id_kategori' => 5, 'nama_kategori' => 'Celana', 'id_pemilik' => 1],
        ]);
    }
}
