<?php

namespace Database\Seeders;

use App\Models\Kasir;
use App\Models\MetodePembayaran;
use App\Models\Pembayaran;
use App\Models\PembayaranPenjualan;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Stok;
use App\Models\TipeTransfer;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Faker\Factory as Faker;
class PenjualanSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('penjualan_detail')->truncate();
        DB::table('pembayaran_penjualan')->truncate();
        DB::table('penjualan')->truncate();
        Schema::enableForeignKeyConstraints();

        $faker = Faker::create();

        $idPemilik = Kasir::find(1)->id_pemilik ?? 1;
        // $startDate = Carbon::create(2025, 1, 1);
        // $endDate = Carbon::create(2025, 12, 31);

        $startDate = Carbon::now()->subYear()->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $pelangganIds = [1, 2, 3, 4, 5]; // id pelanggan dummy

        $counter = 1;

        while ($startDate->lte($endDate)) {
            $jumlahTransaksi = rand(1, 5);
            for ($i = 0; $i < $jumlahTransaksi; $i++) {
                $tanggal = $startDate->copy()->setTime(rand(8, 18), rand(0, 59), 0);

                $produk = $faker->numberBetween(1, 15);
                $jumlah = rand(1, 3);
                $harga = rand(10000, 50000);
                $total = $jumlah * $harga;
                $bayar = $faker->randomElement([$total, $total - rand(5000, 20000)]); // lunas / sebagian

                $metode = $faker->randomElement(['tunai', 'transfer', 'utang']);
                $transfer = ['metode_transfer' => null, 'jenis_transfer' => null];

                if ($metode === 'transfer') {
                    $transfer = $faker->randomElement([
                        ['metode_transfer' => 'Bank', 'jenis_transfer' => 'Bank Rakyat Indonesia'],
                        ['metode_transfer' => 'E-money', 'jenis_transfer' => 'OVO']
                    ]);
                }

                $this->createTransaction([
                    'id_pelanggan' => $faker->randomElement($pelangganIds),
                    'total_harga' => $total,
                    'total_bayar' => $metode === 'utang' ? null : $bayar,
                    'tanggal_penjualan' => $tanggal,
                    'is_pesanan' => $faker->boolean(10), // 10% pesanan
                    'jenis_pembayaran' => $metode,
                    'metode_transfer' => $transfer['metode_transfer'],
                    'jenis_transfer' => $transfer['jenis_transfer'],
                    'diskon' => 0,
                    'details' => [
                        ['id_produk' => $produk, 'jumlah_produk' => $jumlah, 'harga_jual' => $harga]
                    ]
                ], 'INV-' . $idPemilik . $tanggal->format('Ymd') . str_pad($counter++, 3, '0', STR_PAD_LEFT));
            }

            $startDate->addDay();
        }
        $produkIds = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15];
        foreach ($produkIds as $idProduk) {
            $jumlahRestok = rand(50, 100); // stok baru
            DB::table('stok')->insert([
                'id_produk' => $idProduk,
                'jumlah_stok' => $jumlahRestok,
                'jenis_stok' => 'In',
                'jenis_transaksi' => 'Manual',
                'tanggal_stok' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    protected function createTransaction($data, $idPenjualan)
    {
        // Cek stok untuk semua produk terlebih dahulu
        foreach ($data['details'] as $detail) {
            $stokTersedia = Stok::getStokTersediaByProduk($detail['id_produk']);
            if ($stokTersedia < $detail['jumlah_produk']) {
                return; // Skip transaksi jika stok tidak cukup
            }
        }

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