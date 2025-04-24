<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PenjualanSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();

        DB::table('pembayaran')->truncate();
        DB::table('penjualan_detail')->truncate();
        DB::table('penjualan')->truncate();

        Schema::enableForeignKeyConstraints();

        $penjualanData = [
            [
                'id_penjualan' => 'INV-20250412001',
                'id_kasir' => 1,
                'id_pelanggan' => 1,
                'id_bayar_zakat' => null,
                'id_metode_pembayaran' => 1,
                'tanggal_penjualan' => Carbon::parse('2025-04-12 09:00:00'),
                'total_harga' => 50000,
                'status_penjualan' => 'Lunas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_penjualan' => 'INV-20250412002',
                'id_kasir' => 1,
                'id_pelanggan' => 2,
                'id_bayar_zakat' => null,
                'id_metode_pembayaran' => 2,
                'tanggal_penjualan' => Carbon::parse('2025-04-12 10:00:00'),
                'total_harga' => 100000,
                'status_penjualan' => 'Belum Lunas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_penjualan' => 'INV-20250412003',
                'id_kasir' => 2,
                'id_pelanggan' => 3,
                'id_bayar_zakat' => null,
                'id_metode_pembayaran' => 1,
                'tanggal_penjualan' => Carbon::parse('2025-04-12 11:00:00'),
                'total_harga' => 75000,
                'status_penjualan' => 'Lunas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_penjualan' => 'INV-20250412004',
                'id_kasir' => 2,
                'id_pelanggan' => 4,
                'id_bayar_zakat' => null,
                'id_metode_pembayaran' => 3,
                'tanggal_penjualan' => Carbon::parse('2025-04-12 12:00:00'),
                'total_harga' => 60000,
                'status_penjualan' => 'Pesanan',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('penjualan')->insert($penjualanData);

        $penjualanDetailData = [
            ['id_penjualan' => 'INV-20250412001', 'id_produk' => 1, 'jumlah_produk' => 2],
            ['id_penjualan' => 'INV-20250412002', 'id_produk' => 2, 'jumlah_produk' => 1],
            ['id_penjualan' => 'INV-20250412002', 'id_produk' => 3, 'jumlah_produk' => 1],
            ['id_penjualan' => 'INV-20250412003', 'id_produk' => 4, 'jumlah_produk' => 3],
            ['id_penjualan' => 'INV-20250412004', 'id_produk' => 1, 'jumlah_produk' => 1],
        ];

        DB::table('penjualan_detail')->insert($penjualanDetailData);

        $pembayaranData = [
            [
                'id_penjualan' => 'INV-20250412001',
                'id_pembelian' => null,
                'tanggal_pembayaran' => Carbon::parse('2025-04-12 09:30:00'),
                'total_bayar' => 50000,
                'keterangan' => 'Lunas langsung',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_penjualan' => 'INV-20250412002',
                'id_pembelian' => null,
                'tanggal_pembayaran' => Carbon::parse('2025-04-12 10:30:00'),
                'total_bayar' => 50000,
                'keterangan' => 'Bayar sebagian',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_penjualan' => 'INV-20250412003',
                'id_pembelian' => null,
                'tanggal_pembayaran' => Carbon::parse('2025-04-12 11:15:00'),
                'total_bayar' => 75000,
                'keterangan' => 'Lunas',
                'created_at' => now(),
                'updated_at' => now(),
            ]
            // Penjualan ke-4 tidak dibayar (status Pesanan)
        ];

        DB::table('pembayaran')->insert($pembayaranData);
    }
}
