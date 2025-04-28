<?php

namespace App\Http\Controllers;

use App\Models\Kasir;
use Illuminate\Http\Request;
use App\Models\Penjualan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Stok;
use App\Models\MetodePembayaran;
use App\Models\Pembayaran;
use App\Models\TipeTransfer;
use Carbon\Carbon;

class PenjualanController extends Controller
{
    public function barangSudahDiambil($id)
    {
        $penjualan = Penjualan::findOrFail($id);

        // Cek dulu, harus pesanan
        if ($penjualan->status_penjualan !== 'pesanan') {
            return response()->json([
                'message' => 'Penjualan ini bukan pesanan'
            ], 400);
        }

        // Hitung total pembayaran yang sudah dilakukan
        $pembayaran = Pembayaran::where('id_penjualan', $id)->exists();
        if($pembayaran){
            $totalBayar = Pembayaran::where('id_penjualan', $id)->sum('total_bayar');
            // Update status penjualan berdasarkan total pembayaran
            if ($totalBayar >= $penjualan->total_harga) {
                $penjualan->status_penjualan = 'lunas';
            } else {
                $penjualan->status_penjualan = 'belum lunas';
            }
        } else{
            $penjualan->status_penjualan = 'belum lunas';
        }


        $penjualan->save();

        return response()->json([
            'message' => 'Status penjualan berhasil diperbarui',
            'data'    => $penjualan
        ]);
    }
    public function index(Request $request)
    {
        try {
            $idKasir = $request->query('id_kasir');
            $status = $request->query('status');
            $penjualanQuery = Penjualan::with([
                'penjualanDetail.produk', 
                'kasir', 
                'pelanggan', 
                'pembayaran'
            ]);

            if ($status) {
                $penjualanQuery->where('status_penjualan', $request->status);
            }

            if ($idKasir) {
                // 1. Cari id_pemilik dari kasir
                $kasir = Kasir::find($idKasir);
                if ($kasir) {
                    $idPemilik = $kasir->id_pemilik;

                    // 2. Cari semua kasir yg punya id_pemilik sama
                    $idKasirSemua = Kasir::where('id_pemilik', $idPemilik)->pluck('id_kasir');

                    // 3. Tarik semua penjualan dari semua kasir tersebut
                    $penjualanQuery->whereIn('id_kasir', $idKasirSemua);
                }else{
                    return response()->json(['success' => false, 'message' => 'Kasir tidak ditemukan'], 404);
                }
            }

            $penjualans = $penjualanQuery->get();

            // BENTUK ULANG DATA SESUAI YANG DIBUTUHKAN
            $data = $penjualans->map(function ($penjualan) {
                return [
                    'nomor_transaksi' => $penjualan->id_penjualan,
                    'nama_kasir' => $penjualan->kasir?->nama ?? '-',
                    'nama_pelanggan' => $penjualan->pelanggan?->nama ?? '-',
                    'total_harga' => $penjualan->total_harga,
                    'uang_diterima' => $penjualan->pembayaran->sum('total_bayar'),
                    'uang_kembalian' => ($penjualan->pembayaran->sum('total_bayar') - $penjualan->total_harga) < 0 ? 0 : ($penjualan->pembayaran->sum('total_bayar') - $penjualan->total_harga),
                    'sisa_pembayaran' => $penjualan->total_harga - $penjualan->pembayaran->sum('total_bayar') < 0 ? 0 : $penjualan->total_harga - $penjualan->pembayaran->sum('total_bayar'),
                    'diskon' => $penjualan->diskon,
                    'waktu_penjualan' => $penjualan->tanggal_penjualan,
                    'status_penjualan' => $penjualan->status_penjualan,
                    'produk_terjual' => $penjualan->penjualanDetail->map(function ($detail) {
                        return [
                            'nama_produk' => $detail->produk?->id_produk ? $detail->produk->nama_produk : $detail->nama_produk,
                            'jumlah' => $detail->jumlah_produk,
                            'satuan' => $detail->produk?->satuan?->nama_satuan ?? '-', // kalau ada relasi satuan di produk
                            'harga_jual' => $detail->harga_jual ?? 0, // sesuaikan field nya
                            'total' => ($detail->harga_jual ?? 0) * $detail->jumlah_produk
                        ];
                    }),
                    'metode_pembayaran' => $penjualan->pembayaran->map(function ($pembayaran) {
                        return [
                            'metode_pembayaran' => $pembayaran->metode_pembayaran->jenis_pembayaran ?? "-",
                            'tipe' => $pembayaran->metode_pembayaran->tipe_transfer->metode_transfer ?? "-",
                            'jenis' => $pembayaran->metode_pembayaran->tipe_transfer->jenis_transfer ?? "-",
                            'total_bayar' => $pembayaran->total_bayar,
                            'tanggal' => $pembayaran->created_at,
                        ];
                    })
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Data penjualan berhasil diambil.',
                'data' => $data
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data penjualan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $penjualan = Penjualan::with([
                'penjualanDetail.produk.satuan', // include relasi satuan kalau ada
                'kasir',
                'pelanggan',
                'pembayaran.metode_pembayaran.tipe_transfer'
            ])->find($id);

            if (!$penjualan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penjualan tidak ditemukan.'
                ], 404);
            }

            $data = [
                'nomor_transaksi' => $penjualan->id_penjualan,
                'nama_kasir' => $penjualan->kasir?->nama ?? '-',
                'nama_pelanggan' => $penjualan->pelanggan?->nama ?? '-',
                'total_harga' => $penjualan->total_harga,
                'uang_diterima' => $penjualan->pembayaran->sum('total_bayar'),
                'uang_kembalian' => ($penjualan->pembayaran->sum('total_bayar') - $penjualan->total_harga) < 0 ? 0 : ($penjualan->pembayaran->sum('total_bayar') - $penjualan->total_harga),
                'sisa_pembayaran' => ($penjualan->total_harga - $penjualan->pembayaran->sum('total_bayar')) < 0 ? 0 : ($penjualan->total_harga - $penjualan->pembayaran->sum('total_bayar')),
                'diskon' => $penjualan->diskon,
                'waktu_penjualan' => $penjualan->tanggal_penjualan,
                'status_penjualan' => $penjualan->status_penjualan,
                'produk_terjual' => $penjualan->penjualanDetail->map(function ($detail) {
                    return [
                        'nama_produk' => $detail->produk?->id_produk ? $detail->produk->nama_produk : $detail->nama_produk,
                        'jumlah' => $detail->jumlah_produk,
                        'satuan' => $detail->produk?->satuan?->nama_satuan ?? '-',
                        'harga_jual' => $detail->harga_jual ?? 0,
                        'total' => ($detail->harga_jual ?? 0) * $detail->jumlah_produk
                    ];
                }),
                'metode_pembayaran' => $penjualan->pembayaran->map(function ($pembayaran) {
                    return [
                        'metode_pembayaran' => $pembayaran->metode_pembayaran->jenis_pembayaran ?? "-",
                        'tipe' => $pembayaran->metode_pembayaran->tipe_transfer->metode_transfer ?? "-",
                        'jenis' => $pembayaran->metode_pembayaran->tipe_transfer->jenis_transfer ?? "-",
                        'total_bayar' => $pembayaran->total_bayar,
                        'tanggal' => $pembayaran->created_at,
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'message' => 'Detail penjualan berhasil diambil.',
                'data' => $data
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil detail penjualan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cekStok($id_produk)
    {
        $stok = Stok::getStokTersediaByProduk($id_produk);
        return [
            'tersedia' => $stok,
        ];
    }

    public static function validateStok($id_produk, $jumlah_diminta)
    {
        $stok = Stok::getStokTersediaByProduk($id_produk);
        return [
            'tersedia' => $stok,
            'cukup' => $stok >= $jumlah_diminta
        ];
    }

    public function store(Request $request)
    {   
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                // 'id_penjualan' => 'required|string|unique:penjualan,id_penjualan',
                'id_kasir' => 'required|integer',
                'id_pelanggan' => 'required|integer',
                'total_harga' => 'required|numeric|min:0',
                'total_bayar' => 'required_if:jenis_pembayaran,tunai, transfer|numeric|min:0',
                'tanggal_penjualan' => 'required|date',
                'is_pesanan'  => 'required|boolean', // true = pesanan, false = langsung jual
                'jenis_pembayaran' => 'required|in:tunai,transfer,utang',
                'metode_transfer' => 'required_if:jenis_pembayaran,transfer',
                'jenis_transfer' => 'required_if:jenis_pembayaran,transfer',
                'diskon' => 'nullable|integer|min:0',
                'details' => 'required|array|min:1',
                'details.*.jumlah_produk' => 'required|integer',
                'details.*.harga_jual' => 'required|numeric',
            ]);
            foreach ($request->details as $index => $detail) {
                $idProduk = $detail['id_produk'] ?? null;
                $namaProduk = $detail['nama_produk'] ?? null;
            
                if ((empty($idProduk) && empty($namaProduk)) || (!empty($idProduk) && !empty($namaProduk))) {
                    return response()->json([
                        'success' => false,
                        'message' => "Data produk di index ke-{$index} harus diisi salah satu: id_produk atau nama_produk, dan tidak boleh dua-duanya.",
                    ], 422);
                }
            }
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        }
        try {
            // Generate ID Penjualan
            $tanggal = Carbon::now()->format('Ymd'); // contoh: 20250427

            // Hitung jumlah penjualan hari ini
            $jumlahPenjualanHariIni = Penjualan::whereDate('created_at', Carbon::today())->count();

            // Urutan transaksi (ditambah 1 karena mau buat transaksi baru)
            $urutan = str_pad($jumlahPenjualanHariIni + 1, 3, '0', STR_PAD_LEFT);

            // Gabung jadi ID lengkap
            $id_penjualan = 'INV-' . $tanggal . $urutan;
            if ($request->is_pesanan) {
                $status_penjualan = 'pesanan';
            } else {
                $status_penjualan = ($request->total_bayar >= $request->total_harga) ? 'lunas' : 'belum lunas';
            }
            $metodePembayaran = null;
            if (strtolower($request->jenis_pembayaran) == 'transfer') {
                $tipeTransfer = TipeTransfer::where('metode_transfer', $request->metode_transfer)
                    ->where('jenis_transfer', $request->jenis_transfer)
                    ->first();
            
                if (!$tipeTransfer) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Tipe transfer tidak ditemukan.'
                    ], 404);
                }
            
                $metodePembayaran = MetodePembayaran::where('id_tipe_transfer', $tipeTransfer->id_tipe_transfer)
                    ->first();
            
                if (!$metodePembayaran) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Metode pembayaran tidak ditemukan.'
                    ], 404);
                }
            } else if (strtolower($request->jenis_pembayaran) == 'tunai') {
                $metodePembayaran = MetodePembayaran::where('id_tipe_transfer', null)->first();
            } else {
                $metodePembayaran = null;
            }
            // Simpan penjualan
            $penjualan = Penjualan::create([
                'id_penjualan' => $id_penjualan,
                'id_kasir' => $request->id_kasir,
                'id_pelanggan' => $request->id_pelanggan,
                'total_harga' => $request->total_harga,
                'tanggal_penjualan' => $request->tanggal_penjualan,
                'status_penjualan' => $status_penjualan,
                'status_retur' => false,
                'diskon' => $request->diskon
            ]);

            // Simpan detail penjualan dan update stok
            foreach ($request->details as $detail) {
                if (isset($detail['id_produk'])) {
                    $stok_tersedia = self::validateStok($detail['id_produk'], $detail['jumlah_produk']);
                    if (!$stok_tersedia['cukup']) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Stok tidak mencukupi untuk produk ID ' . $detail['id_produk']
                        ], 422);
                    }
                }

                $penjualan->penjualanDetail()->create($detail);

                if (!empty($detail['id_produk'])) {
                    Stok::create([
                        'id_produk' => $detail['id_produk'],
                        'jumlah_stok' => $detail['jumlah_produk'],
                        'jenis_stok' => 'Out',
                        'jenis_transaksi' => $penjualan->id_penjualan,
                        'tanggal_stok' => $request->tanggal_penjualan,
                        'keterangan' => 'Penjualan Produk'
                    ]);
                }
            }

            // Simpan ke tabel pembayaran kalau jenis bukan "utang"
            if (strtolower($request->jenis_pembayaran) != 'utang') {
                Pembayaran::create([
                    'id_penjualan' => $penjualan->id_penjualan,
                    'id_pembelian' => null,
                    'tanggal_pembayaran' => now()->setTimezone('Asia/Jakarta')->format('Y-m-d'),
                    'total_bayar' => $request->total_bayar,
                    'keterangan' => $status_penjualan == 'lunas' ? 'Lunas' : 'Bayar Sebagian',
                    'id_metode_pembayaran' => $metodePembayaran->id_metode_pembayaran
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan.',
                'data' => $penjualan
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan transaksi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function bayarPenjualan(Request $request, $id)
    {
        $request->validate([
            'total_bayar' => 'required|numeric|min:1',
            'jenis_pembayaran' => 'required|in:tunai,transfer',
            'metode_transfer' => 'required_if:jenis_pembayaran,transfer',
            'jenis_transfer' => 'required_if:jenis_pembayaran,transfer',
        ]);

        DB::beginTransaction();

        try {
            $penjualan = Penjualan::findOrFail($id);
            
            // Hitung total bayar sebelum cicilan masuk
            $totalBayarSebelum = Pembayaran::where('id_penjualan', $id)->sum('total_bayar');

            if ($penjualan->status_penjualan === 'lunas' || $totalBayarSebelum >= $penjualan->total_harga) {
                return response()->json([
                    'message' => 'Penjualan sudah lunas. Tidak perlu bayar lagi.'
                ], 400);
            }

            $metodePembayaran = null;
            if (strtolower($request->jenis_pembayaran) == 'transfer') {
                $tipeTransfer = TipeTransfer::where('metode_transfer', $request->metode_transfer)
                    ->where('jenis_transfer', $request->jenis_transfer)
                    ->first();
            
                if (!$tipeTransfer) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Tipe transfer tidak ditemukan.'
                    ], 404);
                }
            
                $metodePembayaran = MetodePembayaran::where('id_tipe_transfer', $tipeTransfer->id_tipe_transfer)
                    ->first();
            
                if (!$metodePembayaran) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Metode pembayaran tidak ditemukan.'
                    ], 404);
                }
            } else if (strtolower($request->jenis_pembayaran) == 'tunai') {
                $metodePembayaran = MetodePembayaran::where('id_tipe_transfer', null)->first();
            } else {
                $metodePembayaran = null;
            }

            // Masukin pembayaran baru
            Pembayaran::create([
                'id_penjualan' => $penjualan->id_penjualan,
                'id_pembelian' => null,
                'tanggal_pembayaran' => now()->setTimezone('Asia/Jakarta')->format('Y-m-d'),
                'total_bayar' => $request->total_bayar,
                'keterangan' => 'Bayar Sebagian',
                'id_metode_pembayaran' => $metodePembayaran->id_metode_pembayaran
            ]);

            // Hitung ulang total bayar setelah cicilan masuk
            $totalBayar = Pembayaran::where('id_penjualan', $id)->sum('total_bayar');

            // Update status penjualan kalau sudah lunas
            if ($totalBayar >= $penjualan->total_harga && $penjualan->status_penjualan !== 'pesanan') {
                $penjualan->status_penjualan = 'lunas';
                $penjualan->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Pembayaran berhasil ditambahkan',
                'data' => $penjualan
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Gagal membayar penjualan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
