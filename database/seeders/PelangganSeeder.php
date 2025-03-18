<?php

namespace Database\Seeders;

use App\Models\Pelanggan;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PelangganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $data = [];
        for ($i = 0; $i < 10; $i++) {
            $data[] = [
                'nama_pelanggan' => $faker->word,
                'id_pemilik' => rand(1, 2),
                'no_telp' => $faker->numerify(str_repeat('#', rand(10, 15))),
                'alamat' => $faker->sentence(10),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Pelanggan::insert($data);
    }
}
