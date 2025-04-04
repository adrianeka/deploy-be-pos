<?php

namespace Database\Seeders;

use App\Models\Kasir;
use App\Models\Pemilik;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $pemilik1 = User::create([
            'name' => 'Pemilik',
            'email' => 'pemilik@example.com',
            'password' => Hash::make('password'),
            'role' => 'pemilik',
        ]);

        $pemilik2 = User::create([
            'name' => 'Pemilik 2',
            'email' => 'pemilik2@example.com',
            'password' => Hash::make('password'),
            'role' => 'pemilik',
        ]);

        $pemilikData1 = Pemilik::create([
            'id_user' => $pemilik1->id,
            'nama_pemilik' => 'Pemilik 1',
            'nama_perusahaan' => 'Toko Nurafie',
            'alamat_toko' => 'Alamat 1',
            'jenis_usaha' => 'Retail',
            'no_telp' => '08123456789'
        ]);

        $pemilikData2 = Pemilik::create([
            'id_user' => $pemilik2->id,
            'nama_pemilik' => 'Pemilik 2',
            'nama_perusahaan' => 'Perusahaan 2',
            'alamat_toko' => 'Alamat 2',
            'jenis_usaha' => 'Grosir',
            'no_telp' => '08987654321'
        ]);

        for ($i = 1; $i <= 10; $i++) {
            $kasirUser = User::create([
                'name' => "Kasir Pemilik 1 - $i",
                'email' => "kasir1_$i@example.com",
                'password' => Hash::make('password'),
                'role' => 'kasir',
            ]);

            Kasir::create([
                'id_user' => $kasirUser->id,
                'id_pemilik' => $pemilikData1->id_pemilik,
                'nama' => "Kasir $i",
                'no_telp' => "08111111$i",
                'alamat' => "Alamat Kasir $i"
            ]);
        }

        for ($i = 1; $i <= 10; $i++) {
            $kasirUser = User::create([
                'name' => "Kasir Pemilik 2 - $i",
                'email' => "kasir2_$i@example.com",
                'password' => Hash::make('password'),
                'role' => 'kasir',
            ]);

            Kasir::create([
                'id_user' => $kasirUser->id,
                'id_pemilik' => $pemilikData2->id_pemilik,
                'nama' => "Kasir $i",
                'no_telp' => "08222222$i",
                'alamat' => "Alamat Kasir $i"
            ]);
        }
    }
}
