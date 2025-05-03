<?php

namespace Database\Seeders;

use App\Models\Kasir;
use App\Models\MetodePembayaran;
use App\Models\Pembayaran;
use App\Models\PembayaranPenjualan;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\TipeTransfer;
use Carbon\Carbon;
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
                'total_harga' => 100000,
                'total_bayar' => 50000,
                'tanggal_penjualan' => Carbon::parse('2025-04-12 10:00:00'),
                'is_pesanan' => false,
                'jenis_pembayaran' => 'transfer',
                'metode_transfer' => 'bank',
                'jenis_transfer' => 'bri',
                'diskon' => 0,
                'details' => [
                    ['id_produk' => 2, 'jumlah_produk' => 1, 'harga_jual' => 50000],
                    ['id_produk' => 3, 'jumlah_produk' => 1, 'harga_jual' => 50000]
                ]
            ],
            [
                'id_pelanggan' => 3,
                'total_harga' => 75000,
                'total_bayar' => 75000,
                'tanggal_penjualan' => Carbon::parse('2025-04-12 11:00:00'),
                'is_pesanan' => true,
                'jenis_pembayaran' => 'transfer',
                'metode_transfer' => 'e-money',
                'jenis_transfer' => 'ovo',
                'diskon' => 0,
                'details' => [
                    ['id_produk' => 4, 'jumlah_produk' => 3, 'harga_jual' => 25000]
                ]
            ],
            [
                'id_pelanggan' => 4,
                'total_harga' => 60000,
                'tanggal_penjualan' => Carbon::parse('2025-04-12 12:00:00'),
                'is_pesanan' => true,
                'jenis_pembayaran' => 'utang',
                'diskon' => 0,
                'details' => [
                    ['id_produk' => 1, 'jumlah_produk' => 1, 'harga_jual' => 50000],
                    ['nama_produk' => 'produk tambah manual', 'jumlah_produk' => 1, 'harga_jual' => 10000]
                ]
            ]
        ];

        // Generate IDs sequentially to avoid duplicates
        $idPemilik = Kasir::find(1)->id_pemilik; // Assuming first kasir represents the pemilik
        $tanggal = Carbon::parse('2025-04-12')->format('Ymd');
        
        foreach ($transactions as $index => $data) {
            $this->createTransaction(
                $data,
                'INV-' . $idPemilik . $tanggal . str_pad($index + 1, 3, '0', STR_PAD_LEFT)
            );
        }
    }

    protected function createTransaction($data, $idPenjualan)
    {
        DB::transaction(function () use ($data, $idPenjualan) {
            // Determine status
            $status = $data['is_pesanan'] 
                ? 'pesanan' 
                : (($data['total_bayar'] ?? 0) >= $data['total_harga'] ? 'lunas' : 'belum lunas');

            // Create penjualan
            $penjualan = Penjualan::create([
                'id_penjualan' => $idPenjualan,
                'id_kasir' => 1,
                'id_pelanggan' => $data['id_pelanggan'],
                'total_harga' => $data['total_harga'],
                'tanggal_penjualan' => $data['tanggal_penjualan'],
                'status_penjualan' => $status,
                'diskon' => $data['diskon'] ?? 0,
                'created_at' => $data['tanggal_penjualan'],
                'updated_at' => $data['tanggal_penjualan']
            ]);

            // Create details
            foreach ($data['details'] as $detail) {
                PenjualanDetail::create(array_merge(
                    ['id_penjualan' => $idPenjualan],
                    $detail
                ));

                if (isset($detail['id_produk'])) {
                    DB::table('stok')->insert([
                        'id_produk' => $detail['id_produk'],
                        'jumlah_stok' => $detail['jumlah_produk'],
                        'jenis_stok' => 'Out',
                        'jenis_transaksi' => $idPenjualan,
                        'tanggal_stok' => $data['tanggal_penjualan'],
                        'keterangan' => 'Penjualan Produk',
                        'created_at' => $data['tanggal_penjualan'],
                        'updated_at' => $data['tanggal_penjualan']
                    ]);
                }
            }

            // Create payment if not utang
            if (strtolower($data['jenis_pembayaran'] ?? '') != 'utang' && isset($data['total_bayar'])) {
                $metodePembayaran = $this->getMetodePembayaran($data);
                
                $pembayaran = Pembayaran::create([
                    'tanggal_pembayaran' => $data['tanggal_penjualan'],
                    'total_bayar' => $data['total_bayar'],
                    'keterangan' => $status == 'lunas' ? 'Lunas' : 'Bayar Sebagian',
                    'id_metode_pembayaran' => $metodePembayaran->id_metode_pembayaran,
                    'created_at' => $data['tanggal_penjualan'],
                    'updated_at' => $data['tanggal_penjualan']
                ]);

                PembayaranPenjualan::create([
                    'id_penjualan' => $idPenjualan,
                    'id_pembayaran' => $pembayaran->id_pembayaran,
                    'created_at' => $data['tanggal_penjualan'],
                    'updated_at' => $data['tanggal_penjualan']
                ]);
            }
        });
    }

    protected function getMetodePembayaran($data)
    {
        if (strtolower($data['jenis_pembayaran']) == 'transfer') {
            $tipeTransfer = TipeTransfer::where('metode_transfer', $data['metode_transfer'])
                ->where('jenis_transfer', $data['jenis_transfer'])
                ->first();
            if (!$tipeTransfer) {
                throw new \Exception("Tipe transfer tidak ditemukan: metode = {$data['metode_transfer']}, jenis = {$data['jenis_transfer']}");
            }
            
            return MetodePembayaran::where('id_tipe_transfer', $tipeTransfer->id_tipe_transfer)
                ->first();
        }
        
        return MetodePembayaran::whereNull('id_tipe_transfer')->first();
    }
}