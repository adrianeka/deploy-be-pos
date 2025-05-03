<?php

namespace Database\Seeders;

use App\Models\TipeTransfer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipeTransferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TipeTransfer::insert([
            ['metode_transfer' => 'Bank', 'jenis_transfer' => 'Bank Central Asia'],
            ['metode_transfer' => 'Bank', 'jenis_transfer' => 'Mandiri'],
            ['metode_transfer' => 'Bank', 'jenis_transfer' => 'Bank Rakyat Indonesia'],
            ['metode_transfer' => 'E-money', 'jenis_transfer' => 'OVO'],
            ['metode_transfer' => 'E-money', 'jenis_transfer' => 'DANA'],
            ['metode_transfer' => 'E-money', 'jenis_transfer' => 'SHOPEEPAY'],
            ['metode_transfer' => 'E-money', 'jenis_transfer' => 'GOPAY'],
        ]);
    }
}
