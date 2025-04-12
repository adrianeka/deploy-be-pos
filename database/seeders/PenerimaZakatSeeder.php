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
        $faker = Faker::create('id_ID');

        $data = [];
        for ($i = 0; $i < 10; $i++) {
            $nama = $faker->name;
            $length = rand(11, 13);
            $no_telp = '08' . $faker->numerify(str_repeat('#', $length - 2));
            $no_rekening = $faker->numerify(str_repeat('#', 16));

            $data[] = [
                'id_pemilik' => 1,
                'nama_penerima' => $nama,
                'no_telp' => $no_telp,
                'no_rekening' => $no_rekening,
                'nama_bank' => $faker->randomElement(['BRI', 'BCA', 'BNI', 'Mandiri', 'BSI']),
                'rekening_atas_nama' => $nama,
                'alamat' => $faker->address,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        PenerimaZakat::insert($data);
    }
}
