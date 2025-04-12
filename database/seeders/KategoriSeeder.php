<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    public function run()
    {
        DB::table('kategori')->insert([
            ['id_kategori' => 1, 'nama_kategori' => 'Sajadah', 'id_pemilik' => 1],
            ['id_kategori' => 2, 'nama_kategori' => 'Sorban', 'id_pemilik' => 1],
            ['id_kategori' => 3, 'nama_kategori' => 'Kurma', 'id_pemilik' => 1],
            ['id_kategori' => 4, 'nama_kategori' => 'Air Zamzam', 'id_pemilik' => 1],
            ['id_kategori' => 5, 'nama_kategori' => 'Peci Haji', 'id_pemilik' => 1],
            ['id_kategori' => 6, 'nama_kategori' => 'Mukena', 'id_pemilik' => 1],
            ['id_kategori' => 7, 'nama_kategori' => 'Oleh-oleh Haji', 'id_pemilik' => 1],
        ]);
    }
}
