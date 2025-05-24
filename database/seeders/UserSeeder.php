<?php

namespace Database\Seeders;

use App\Models\Kasir;
use App\Models\Pemilik;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Pemilik 1
        $pemilik1 = User::create([
            'name' => 'Nurafie',
            'email' => 'nurafie@tokohaji.com',
            'password' => Hash::make('password'),
            'role' => 'pemilik',
        ]);

        $pemilikData1 = Pemilik::create([
            'id_user' => $pemilik1->id,
            'nama_pemilik' => 'Nurafie',
            'nama_perusahaan' => 'Toko Oleh-Oleh Haji Nurafie',
            'alamat_toko' => 'Jl. Mekah No. 17, Bandung',
            'jenis_usaha' => 'Retail Oleh-Oleh Haji',
            'no_telp' => '081234567890'
        ]);

        // Pemilik 2
        $pemilik2 = User::create([
            'name' => 'Zainal Abidin',
            'email' => 'zainal@tokohaji.com',
            'password' => Hash::make('password'),
            'role' => 'pemilik',
        ]);

        $pemilikData2 = Pemilik::create([
            'id_user' => $pemilik2->id,
            'nama_pemilik' => 'Zainal Abidin',
            'nama_perusahaan' => 'Pusat Oleh-Oleh Haji Barokah',
            'alamat_toko' => 'Jl. Madinah No. 25, Bekasi',
            'jenis_usaha' => 'Retail dan Grosir',
            'no_telp' => '082345678901'
        ]);

        // Kasir untuk Toko Nurafie
        $kasir1 = User::create([
            'name' => 'Ahmad Fadillah',
            'email' => 'kasir.nurafie@tokohaji.com',
            'password' => Hash::make('password'),
            'role' => 'kasir',
        ]);

        Kasir::create([
            'id_user' => $kasir1->id,
            'id_pemilik' => $pemilikData1->id_pemilik,
            'nama' => 'Ahmad Fadillah',
            'no_telp' => '081111112222',
            'alamat' => 'Kp. Kauman, Bandung'
        ]);

        $kasir2 = User::create([
            'name' => 'Rendi Rohmah',
            'email' => 'kasir2.nurafie@tokohaji.com',
            'password' => Hash::make('password'),
            'role' => 'kasir',
        ]);

        Kasir::create([
            'id_user' => $kasir2->id,
            'id_pemilik' => $pemilikData1->id_pemilik,
            'nama' => 'Rendi Rohmah',
            'no_telp' => '082222223333',
            'alamat' => 'Jl. Cibitung No. 9, Bekasi'
        ]);

        $kasir3 = User::create([
            'name' => 'Muhammad Rizki',
            'email' => 'kasir.barokah@tokohaji.com',
            'password' => Hash::make('password'),
            'role' => 'kasir',
        ]);

        Kasir::create([
            'id_user' => $kasir3->id,
            'id_pemilik' => $pemilikData2->id_pemilik,
            'nama' => 'Muhammad Rizki',
            'no_telp' => '082266297533',
            'alamat' => 'Jl. Gegerkalong No. 71, Bandung'
        ]);
    }
}
