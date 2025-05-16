<?php

namespace Database\Seeders;

use App\Models\LevelHarga;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PenjualanSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('penjualan_detail')->truncate();
        DB::table('pembayaran_penjualan')->truncate();
        DB::table('penjualan')->truncate();
        Schema::enableForeignKeyConstraints();

        // Create sample transactions
        $transactions = [
            [
                'id_pelanggan' => 1,
<<<<<<< HEAD
=======
                'id_bayar_zakat' => null,
>>>>>>> dev-adi
                'total_harga' => 50000,
                'total_bayar' => 50000,
                'tanggal_penjualan' => Carbon::parse('2025-04-12 09:00:00'),
                'is_pesanan' => false,
                'jenis_pembayaran' => 'tunai',
                'diskon' => 0,
                'details' => [
                    ['id_produk' => 1, 'jumlah_produk' => 2, 'harga_jual' => 25000]
                ]
            ],
            [
                'id_pelanggan' => 2,
<<<<<<< HEAD
=======
                'id_bayar_zakat' => null,
>>>>>>> dev-adi
                'total_harga' => 100000,
                'total_bayar' => 50000,
                'tanggal_penjualan' => Carbon::parse('2025-04-12 10:00:00'),
                'is_pesanan' => false,
                'jenis_pembayaran' => 'transfer',
                'metode_transfer' => 'Bank',
                'jenis_transfer' => 'Bank Rakyat Indonesia',
                'diskon' => 0,
                'details' => [
                    ['id_produk' => 2, 'jumlah_produk' => 1, 'harga_jual' => 50000],
                    ['id_produk' => 3, 'jumlah_produk' => 1, 'harga_jual' => 50000]
                ]
            ],
            [
                'id_pelanggan' => 3,
<<<<<<< HEAD
=======
                'id_bayar_zakat' => null,
>>>>>>> dev-adi
                'total_harga' => 75000,
                'total_bayar' => 75000,
                'tanggal_penjualan' => Carbon::parse('2025-04-12 11:00:00'),
                'is_pesanan' => true,
                'jenis_pembayaran' => 'transfer',
                'metode_transfer' => 'E-money',
                'jenis_transfer' => 'OVO',
                'diskon' => 0,
                'details' => [
                    ['id_produk' => 4, 'jumlah_produk' => 3, 'harga_jual' => 25000]
                ]
            ],
            [
                'id_pelanggan' => 4,
<<<<<<< HEAD
                'total_harga' => 60000,
                'tanggal_penjualan' => Carbon::parse('2025-04-12 12:00:00'),
=======
                'id_bayar_zakat' => null,
>>>>>>> dev-adi
                'total_harga' => 60000,
                'status_penjualan' => 'pesanan',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('penjualan')->insert($penjualanData);

        $penjualanDetailData = [
            ['id_penjualan' => 'INV-20250412001', 'id_produk' => 1, 'nama_produk' => null, 'jumlah_produk' => 2, 'harga_jual' => $this->getHargaJual(1, 'Standart')],
            ['id_penjualan' => 'INV-20250412002', 'id_produk' => 2, 'nama_produk' => null, 'jumlah_produk' => 1, 'harga_jual' => $this->getHargaJual(2, 'Standart')],
            ['id_penjualan' => 'INV-20250412002', 'id_produk' => 3, 'nama_produk' => null, 'jumlah_produk' => 1, 'harga_jual' => $this->getHargaJual(3, 'Standart')],
            ['id_penjualan' => 'INV-20250412003', 'id_produk' => 4, 'nama_produk' => null, 'jumlah_produk' => 3, 'harga_jual' => $this->getHargaJual(4, 'Standart')],
            ['id_penjualan' => 'INV-20250412004', 'id_produk' => 1, 'nama_produk' => null, 'jumlah_produk' => 1, 'harga_jual' => $this->getHargaJual(1, 'Standart')],
            ['id_penjualan' => 'INV-20250412004', 'id_produk' => null, 'nama_produk' => 'produk tambah manual', 'jumlah_produk' => 1, 'harga_jual' => 10000]
        ];

        DB::table('penjualan_detail')->insert($penjualanDetailData);

        $pembayaranData = [
            [
                'id_pembayaran' => 1,
                'id_tipe_transfer' => null, // Tunai
                'jenis_pembayaran' => 'tunai',
                'total_bayar' => 50000,
                'keterangan' => 'lunas langsung',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_pembayaran' => 2,
                'id_tipe_transfer' => 1, // Contoh BCA
                'jenis_pembayaran' => 'transfer',
                'total_bayar' => 50000,
                'keterangan' => 'Bayar sebagian',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_pembayaran' => 3,
                'id_tipe_transfer' => 2, // Contoh Mandiri
                'jenis_pembayaran' => 'transfer',
                'total_bayar' => 75000,
                'keterangan' => 'lunas',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];


        DB::table('pembayaran')->insert($pembayaranData);

        $pembayaranPenjualanData = [
            [
                'id_pembayaran' => 1,
                'id_penjualan' => 'INV-20250412001',
            ],
            [
                'id_pembayaran' => 2,
                'id_penjualan' => 'INV-20250412002',
            ],
            [
                'id_pembayaran' => 3,
                'id_penjualan' => 'INV-20250412003',
            ]
        ];

        DB::table('pembayaran_penjualan')->insert($pembayaranPenjualanData);
    }

    private function getHargaJual($idProduk, $levelHarga)
    {
        $harga = LevelHarga::where('id_produk', $idProduk)
            ->where('nama_level', $levelHarga)
            ->value('harga_jual');

        return $harga ?? 0; // Jika tidak ada harga, fallback ke 0
    }
}
