<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class BayarZakatSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // Penerima Zakat 1
            [
                'id_pemilik' => 1,
                'id_penerima_zakat' => 1,
                'modal_terjual' => 1000000,
                'tanggal_bayar' => Carbon::parse('2025-04-01 10:00:00'),
            ],
            [
                'id_pemilik' => 1,
                'id_penerima_zakat' => 1,
                'modal_terjual' => 1500000,
                'tanggal_bayar' => Carbon::parse('2025-04-05 12:15:00'),
            ],

            // Penerima Zakat 2
            [
                'id_pemilik' => 1,
                'id_penerima_zakat' => 2,
                'modal_terjual' => 800000,
                'tanggal_bayar' => Carbon::parse('2025-03-28 09:30:00'),
            ],
            [
                'id_pemilik' => 1,
                'id_penerima_zakat' => 2,
                'modal_terjual' => 1200000,
                'tanggal_bayar' => Carbon::parse('2025-04-07 11:00:00'),
            ],
            [
                'id_pemilik' => 1,
                'id_penerima_zakat' => 2,
                'modal_terjual' => 600000,
                'tanggal_bayar' => Carbon::parse('2025-04-10 08:45:00'),
            ],

            // Penerima Zakat 3
            [
                'id_pemilik' => 1,
                'id_penerima_zakat' => 3,
                'modal_terjual' => 900000,
                'tanggal_bayar' => Carbon::parse('2025-03-25 13:20:00'),
            ],
            [
                'id_pemilik' => 1,
                'id_penerima_zakat' => 3,
                'modal_terjual' => 1300000,
                'tanggal_bayar' => Carbon::parse('2025-04-03 14:10:00'),
            ],
        ];

        // Tambahkan nominal_zakat secara otomatis
        foreach ($data as &$item) {
            $item['nominal_zakat'] = $item['modal_terjual'] * 0.025;
            $item['created_at'] = now();
            $item['updated_at'] = now();
        }

        DB::table('bayar_zakat')->insert($data);
    }
}
