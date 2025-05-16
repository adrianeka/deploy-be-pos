<?php

namespace Database\Seeders;

use App\Models\TipeTransfer;
use Illuminate\Database\Seeder;

class TipeTransferSeeder extends Seeder
{
    public function run(): void
    {
        $banks = [
            'BCA',
            'Mandiri',
            'BRI',
            'BNI',
            'BTN',
            'CIMB Niaga',
            'Danamon',
            'Permata',
            'Panin Bank',
            'OCBC NISP',
            'BTPN',
            'Bank Muamalat',
            'Bank Syariah Indonesia (BSI)',
            'Bank Jago',
            'Bank Mega',
            'Bank Bukopin',
            'Bank Mayapada',
            'Bank Sinarmas',
            'Bank BJB (Jabar Banten)',
            'Bank DKI',
            'Bank Jatim',
            'Bank Jateng',
            'Bank Sumut',
            'Bank Sumsel Babel',
            'Bank Kaltimtara',
            'Bank Kalteng',
            'Bank Kalsel',
            'Bank NTB Syariah',
            'Bank NTT',
            'Bank Papua',
            'Bank Riau Kepri Syariah',
            'Bank Maluku Malut',
            'Bank Bengkulu',
            'Bank Lampung',
            'Bank SulutGo',
            'Bank Sulteng',
            'Bank Sulselbar',
            'Bank Nagari',
            'Bank Aceh Syariah',
        ];

        $eMonies = [
            'GoPay',
            'OVO',
            'DANA',
            'ShopeePay',
            'LinkAja',
            'iSaku',
            'Sakuku',
            'Paytren',
            'TrueMoney',
            'Jenius Pay',
            'SPIN (MNC Bank)',
            'DOKU Wallet',
            'XL Tunai',
            'Mandiri E-Cash',
            'T-Cash (Telkomsel)',
            'Bima Money',
            'BRIZZI (BRI)',
            'TapCash (BNI)',
            'Flazz (BCA)',
            'e-Money (Mandiri)',
            'JakCard (Bank DKI)',
        ];

        $data = [];

        foreach ($banks as $bank) {
            $data[] = [
                'metode_transfer' => 'bank',
                'jenis_transfer' => $bank,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach ($eMonies as $eMoney) {
            $data[] = [
                'metode_transfer' => 'e-money',
                'jenis_transfer' => $eMoney,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        TipeTransfer::insert($data);
    }
}
