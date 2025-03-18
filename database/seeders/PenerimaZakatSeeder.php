<?php

namespace Database\Seeders;

use App\Models\PenerimaZakat;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PenerimaZakatSeeder extends Seeder
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
                'id_pemilik' => rand(1, 2),
                'nama_penerima' => $faker->word,
                'no_telp' => $faker->numerify(str_repeat('#', rand(10, 15))),
                'no_rekening' => $faker->numerify(str_repeat('#', 16)),
                'nama_bank' => $faker->randomElement(['BRI', 'BCA', 'BNI']),
                'rekening_atas_nama' => $faker->word,
                'alamat' => $faker->sentence(10),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        PenerimaZakat::insert($data);
    }
}
