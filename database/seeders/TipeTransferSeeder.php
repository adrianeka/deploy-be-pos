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
            ['metode_transfer' => 'bank', 'jenis_transfer' => 'bca'],
            ['metode_transfer' => 'bank', 'jenis_transfer' => 'mandiri'],
            ['metode_transfer' => 'bank', 'jenis_transfer' => 'bri'],
            ['metode_transfer' => 'e-money', 'jenis_transfer' => 'ovo'],
            ['metode_transfer' => 'e-money', 'jenis_transfer' => 'dana'],
            ['metode_transfer' => 'e-money', 'jenis_transfer' => 'shopeepay'],
            ['metode_transfer' => 'e-money', 'jenis_transfer' => 'gopay'],
        ]);
    }
}
