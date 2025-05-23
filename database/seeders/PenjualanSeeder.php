<?php

namespace Database\Seeders;

use App\Models\Kasir;
use App\Models\LevelHarga;
use App\Models\Pembayaran;
use App\Models\PembayaranPenjualan;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
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
        DB::table('stok')->where('jenis_stok', 'Out')->delete(); // Hapus stok keluar saja
        Schema::enableForeignKeyConstraints();

        $faker = Faker::create();

        // 1. Buat stok unlimited sementara
        $produks = Produk::with('level_hargas')->get();
        foreach ($produks as $produk) {
            DB::table('stok')->insert([
                'id_produk' => $produk->id_produk,
                'jumlah_stok' => 999999, // Stok sangat besar
                'jenis_stok' => 'In',
                'jenis_transaksi' => 'Manual',
                'created_at' => now()->subYear(),
                'updated_at' => now()
            ]);
        }

        // 2. Proses seeding transaksi tanpa pengecekan stok
        $idPemilik = Kasir::find(1)->id_pemilik ?? 1;
        $startDate = Carbon::now()->subYear()->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        $pelangganIds = [1, 2, 3, 4, 5];
        $currentDay = null;
        $dailyCounter = 1;

        // Level harga yang tersedia (untuk pemilihan acak)
        $levelNames = ['Standart'];

        while ($startDate->lte($endDate)) {
            // Reset counter if it's a new day
            if ($currentDay != $startDate->format('Ymd')) {
                $currentDay = $startDate->format('Ymd');
                $dailyCounter = 1;
            }

            $jumlahTransaksi = rand(1, 5);
            for ($i = 0; $i < $jumlahTransaksi; $i++) {
                $tanggal = $startDate->copy()->setTime(rand(8, 18), rand(0, 59), 0);

                // Get random product
                $produk = $produks->random();
                $jumlah = rand(1, 3);

                // Get a random level price or standart by default
                $levelName = $faker->randomElement($levelNames);

                // Find the level price record
                $levelHarga = $produk->level_hargas()
                    ->whereRaw('LOWER(nama_level) = ?', [strtolower($levelName)])
                    ->first();

                // If level price not found, use standart or calculate from harga_beli
                if (!$levelHarga) {
                    $levelHarga = $produk->level_hargas()
                        ->whereRaw('LOWER(nama_level) = ?', ['standart'])
                        ->first();

                    // If still not found, calculate a price
                    if (!$levelHarga) {
                        $harga = $produk->harga_beli * 1.2;
                        $harga = round($harga / 1000) * 1000;
                        $idLevelHarga = null;
                    } else {
                        $harga = $levelHarga->harga_jual;
                        $idLevelHarga = $levelHarga->id_level_harga;
                    }
                } else {
                    $harga = $levelHarga->harga_jual;
                    $idLevelHarga = $levelHarga->id_level_harga;
                }

                $total = $jumlah * $harga;
                $bayar = $faker->randomElement([$total, $total - rand(5000, 20000)]);

                $metode = $faker->randomElement(['tunai', 'transfer', 'utang']);
                $transfer = ['metode_transfer' => null, 'jenis_transfer' => null];

                if ($metode === 'transfer') {
                    $transfer = $faker->randomElement([
                        ['metode_transfer' => 'Bank', 'jenis_transfer' => 'BRI'],
                        ['metode_transfer' => 'E-wallet', 'jenis_transfer' => 'OVO']
                    ]);
                }

                $this->createTransaction([
                    'id_pelanggan' => $faker->randomElement($pelangganIds),
                    'total_harga' => $total,
                    'total_bayar' => $metode === 'utang' ? null : $bayar,
                    'tanggal_penjualan' => $tanggal,
                    'is_pesanan' => $faker->boolean(10),
                    'jenis_pembayaran' => $metode,
                    'metode_transfer' => $transfer['metode_transfer'],
                    'jenis_transfer' => $transfer['jenis_transfer'],
                    'diskon' => 0,
                    'details' => [
                        [
                            'id_produk' => $produk->id_produk,
                            'jumlah_produk' => $jumlah,
                            'harga_jual' => $harga
                        ]
                    ]
                ], 'INV-' . $idPemilik . $tanggal->format('Ymd') . str_pad($dailyCounter++, 3, '0', STR_PAD_LEFT));
            }
            $startDate->addDay();
        }

        // 3. Setelah semua transaksi dibuat, perbaiki stok jadi realistis
        $this->normalizeStock();
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
                'status_penjualan' => $status,
                'diskon' => $data['diskon'] ?? 0,
                'created_at' => $data['tanggal_penjualan'],
                'updated_at' => $data['tanggal_penjualan']
            ]);

            // Create details
            foreach ($data['details'] as $detail) {
                // Create penjualan detail (without id_level_harga as it doesn't exist in table)
                PenjualanDetail::create([
                    'id_penjualan' => $idPenjualan,
                    'id_produk' => $detail['id_produk'],
                    'jumlah_produk' => $detail['jumlah_produk'],
                    'harga_jual' => $detail['harga_jual'],
                    'status_retur' => 0
                ]);

                if (isset($detail['id_produk'])) {
                    DB::table('stok')->insert([
                        'id_produk' => $detail['id_produk'],
                        'jumlah_stok' => $detail['jumlah_produk'],
                        'jenis_stok' => 'Out',
                        'jenis_transaksi' => $idPenjualan,
                        'created_at' => $data['tanggal_penjualan'],
                        'updated_at' => $data['tanggal_penjualan']
                    ]);
                }
            }

            // Create payment if not utang
            if (strtolower($data['jenis_pembayaran'] ?? '') != 'utang' && isset($data['total_bayar'])) {
                $tipeTransfer = $this->getMetodePembayaran($data);
                
                $pembayaran = Pembayaran::create([
                    'total_bayar' => $data['total_bayar'],
                    'keterangan' => $status == 'lunas' ? 'Lunas' : 'Bayar Sebagian',
                    'id_tipe_transfer' => $tipeTransfer->id_tipe_transfer ?? null,
                    'jenis_pembayaran' => $data['jenis_pembayaran'],
                    'id_tipe_transfer' => $tipeTransfer ? $tipeTransfer->id_tipe_transfer : null,
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

    protected function normalizeStock()
    {
        // 1. Hitung total stok keluar per produk
        $stokKeluar = DB::table('stok')
            ->where('jenis_stok', 'Out')
            ->select('id_produk', DB::raw('SUM(jumlah_stok) as total_keluar'))
            ->groupBy('id_produk')
            ->get()
            ->keyBy('id_produk');

        // 2. Update stok masuk dengan nilai yang realistis
        foreach ($stokKeluar as $produkId => $keluar) {
            $stokMasuk = $keluar->total_keluar + rand(20, 50); // Stok masuk = stok keluar + buffer

            DB::table('stok')
                ->where('id_produk', $produkId)
                ->where('jenis_stok', 'In')
                ->update(['jumlah_stok' => $stokMasuk]);
        }
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
            return $tipeTransfer;
        } else {
            return null;
        }
    }
}
