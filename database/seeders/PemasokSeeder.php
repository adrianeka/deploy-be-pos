<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\Pemasok;

class PemasokSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $data = [];
        for ($i = 0; $i < 10; $i++) {
            $length = rand(11, 13);
            $no_telp = '08' . $faker->numerify(str_repeat('#', $length - 2));

            $data[] = [
                'nama_perusahaan' => $faker->company,
                'id_pemilik' => 1,
                'no_telp' => $no_telp,
                'alamat' => $faker->address,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Pemasok::insert($data);
    }
}
