<?php

namespace App\Http\Controllers;

use App\Models\Kasir;
use App\Models\LevelHarga;
use Illuminate\Http\Request;
use App\Models\Penjualan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Stok;
use App\Models\MetodePembayaran;
use App\Models\Pembayaran;
use App\Models\PembayaranPenjualan;
use App\Models\TipeTransfer;
use Carbon\Carbon;

class PenjualanController extends Controller
{
    public function barangSudahDiambil($id)
    {
        $penjualan = Penjualan::findOrFail($id);

        if ($penjualan->status_penjualan !== 'pesanan') {
            return response()->json(['message' => 'Penjualan ini bukan pesanan'], 400);
        }

        $pembayaranExists = PembayaranPenjualan::where('id_penjualan', $id)->exists();
        $penjualan->status_penjualan = $pembayaranExists 
            ? ($penjualan->uang_diterima >= $penjualan->total_harga ? 'lunas' : 'belum lunas')
            : 'belum lunas';

        $penjualan->save();

        return response()->json([
            'message' => 'Status penjualan berhasil diperbarui',
            'data' => $penjualan
        ]);
    }

    public function index(Request $request)
    {
        try {
            $penjualanQuery = Penjualan::with('kasir');

            if ($status = $request->query('status')) {
                $penjualanQuery->where('status_penjualan', $status);
            }
            
            if ($idKasir = $request->query('id_kasir')) {
                $kasir = Kasir::findOrFail($idKasir);
                $penjualanQuery->whereIn('id_kasir', 
                    Kasir::where('id_pemilik', $kasir->id_pemilik)->pluck('id_kasir'));
            }

            $data = $penjualanQuery->get()->map(function ($penjualan) {
                return [
                    'nomor_transaksi' => $penjualan->id_penjualan,
                    'total_harga' => $penjualan->total_harga,
                    'tanggal_penjualan' => $penjualan->tanggal_penjualan,
                    'status_penjualan' => $penjualan->status_penjualan,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Data penjualan berhasil diambil.',
                'data' => $data
            ]);

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
                'penjualanDetail.produk.satuan',
                'kasir',
                'pelanggan',
                'pembayaran.metode_pembayaran.tipe_transfer'
            ])->findOrFail($id);

            $data = [
                'nomor_transaksi' => $penjualan->id_penjualan,
                'nama_kasir' => $penjualan->kasir?->nama ?? '-',
                'nama_pelanggan' => $penjualan->pelanggan?->nama_pelanggan ?? '-',
                'total_harga' => $penjualan->total_harga,
                'uang_diterima' => $penjualan->uang_diterima,
                'uang_kembalian' => $penjualan->uang_kembalian,
                'sisa_pembayaran' => $penjualan->sisa_pembayaran,
                'diskon' => $penjualan->diskon,
                'waktu_penjualan' => $penjualan->tanggal_penjualan,
                'status_penjualan' => $penjualan->status_penjualan,
                'produk_terjual' => $penjualan->penjualanDetail->map(function ($detail) {
                    return [
                        'id_produk' => $detail->produk?->id_produk ?? $detail->id_produk,
                        'nama_produk' => $detail->produk?->id_produk ? $detail->produk->nama_produk : $detail->nama_produk,
                        'jumlah' => $detail->jumlah_produk,
                        'satuan' => $detail->produk?->satuan?->nama_satuan ?? '-',
                        'harga_jual' => $detail->harga_jual ?? 0,
                        'total' => ($detail->harga_jual ?? 0) * $detail->jumlah_produk,
                        'status_retur' => $detail->status_retur
                    ];
                }),
                'pembayaran' => $penjualan->pembayaran->map(function ($pembayaran) {
                    return [
                        'metode_pembayaran' => $pembayaran->metode_pembayaran?->jenis_pembayaran ?? "-",
                        'tipe' => $pembayaran->metode_pembayaran?->tipe_transfer?->metode_transfer ?? "-",
                        'jenis' => $pembayaran->metode_pembayaran?->tipe_transfer?->jenis_transfer ?? "-",
                        'total_bayar' => $pembayaran->total_bayar ?? 0,
                        'tanggal' => $pembayaran->created_at ?? "-"
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'message' => 'Detail penjualan berhasil diambil.',
                'data' => $data
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil detail penjualan.',
                'error' => $e->getMessage()
            ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
        }
    }

    public function cekStok($id_produk)
    {
        return ['tersedia' => Stok::getStokTersediaByProduk($id_produk)];
    }

    public static function validateStok($id_produk, $jumlah_diminta)
    {
        $stok = Stok::getStokTersediaByProduk($id_produk);
        return ['tersedia' => $stok, 'cukup' => $stok >= $jumlah_diminta];
    }

    public function store(Request $request)
    {   
        DB::beginTransaction();
        
        try {
            $validated = $request->validate([
                'id_kasir' => 'required|integer',
                'id_pelanggan' => 'nullable|integer',
                'total_harga' => 'required|numeric|min:0',
                'total_bayar' => 'required_if:jenis_pembayaran,tunai,transfer|numeric|min:0',
                'is_pesanan' => 'required|boolean',
                'jenis_pembayaran' => 'required|in:tunai,transfer,kasbon',
                'metode_transfer' => 'required_if:jenis_pembayaran,transfer',
                'jenis_transfer' => 'required_if:jenis_pembayaran,transfer',
                'diskon' => 'nullable|integer|min:0',
                'details' => 'required|array|min:1',
                'details.*.jumlah_produk' => 'required|integer',
                'details.*.harga_jual' => 'required|numeric',
            ]);

            $kasir = Kasir::findOrFail($validated['id_kasir']);
            $idPemilik = $kasir->id_pemilik;
            $tanggal_penjualan = Carbon::now();

            // Validate product details
            foreach ($request->details as $index => $detail) {
                if (!isset($detail['id_produk']) && !isset($detail['nama_produk'])) {
                    return response()->json([
                        'success' => false,
                        'message' => "Data produk di index ke-{$index} harus diisi salah satu: id_produk atau nama_produk."
                    ], 422);
                }
            }

            // Generate ID Penjualan
            $count = Penjualan::whereDate('created_at', Carbon::today())
                ->whereHas('kasir', fn($q) => $q->where('id_pemilik', $idPemilik))
                ->count();
                
            $id_penjualan = 'INV-' . $idPemilik . Carbon::now()->format('Ymd') . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

            $status_penjualan = $request->is_pesanan 
                ? 'pesanan' 
                : ($request->total_bayar >= $request->total_harga ? 'lunas' : 'belum lunas');

            $metodePembayaran = $this->getMetodePembayaran($request);

            // Create penjualan
            $penjualan = Penjualan::create([
                'id_penjualan' => $id_penjualan,
                'id_kasir' => $request->id_kasir,
                'id_pelanggan' => $request->id_pelanggan,
                'total_harga' => $request->total_harga,
                'tanggal_penjualan' => $tanggal_penjualan,
                'status_penjualan' => $status_penjualan,
                'diskon' => $request->diskon
            ]);

            // Process details
            $this->processPenjualanDetails($request->details, $penjualan, $tanggal_penjualan);

            // Process payment if not kasbon
            if (strtolower($request->jenis_pembayaran) != 'kasbon') {
                $pembayaran = Pembayaran::create([
                    'tanggal_pembayaran' => Carbon::now()->setTimezone('Asia/Jakarta')->format('Y-m-d'),
                    'total_bayar' => $request->total_bayar,
                    'keterangan' => $status_penjualan == 'lunas' ? 'Lunas' : 'Bayar Sebagian',
                    'id_metode_pembayaran' => $metodePembayaran->id_metode_pembayaran
                ]);

                PembayaranPenjualan::create([
                    'id_penjualan' => $penjualan->id_penjualan,
                    'id_pembayaran' => $pembayaran->id_pembayaran,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan.',
                'data' => $penjualan
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
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
            
            if ($penjualan->status_penjualan === 'lunas') {
                return response()->json(['message' => 'Penjualan sudah lunas.'], 400);
            }
            
            $metodePembayaran = $this->getMetodePembayaran($request);

            $pembayaran = Pembayaran::create([
                'id_penjualan' => $penjualan->id_penjualan,
                'tanggal_pembayaran' => Carbon::now()->setTimezone('Asia/Jakarta')->format('Y-m-d'),
                'total_bayar' => $request->total_bayar,
                'keterangan' => 'Bayar Sebagian',
                'id_metode_pembayaran' => $metodePembayaran->id_metode_pembayaran
            ]);

            PembayaranPenjualan::create([
                'id_penjualan' => $penjualan->id_penjualan,
                'id_pembayaran' => $pembayaran->id_pembayaran,
            ]);

            if ($penjualan->uang_diterima >= $penjualan->total_harga && $penjualan->status_penjualan !== 'pesanan') {
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

    public function getLevelHargas(Request $request){
        try{
            $levelHargaQuery = LevelHarga::query();

            if($idProduk = request('id_produk')) {
                $levelHargaQuery->where('id_produk', $idProduk);
            }

            return response()->json([
                'success' => true,
                'message' => 'Level Harga berhasil didapatkan',
                'data' => $levelHargaQuery->get()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal mendapatkan level harga',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllMetodePembayaran(Request $request){
        try {
            $metodePembayaranQuery = TipeTransfer::query();

            if ($metodeTransfer = request('metode_transfer')) {
                $metodePembayaranQuery->where('metode_transfer', $metodeTransfer);
            }

            return response()->json([
                'success' => true,
                'message' => 'Metode Pembayaran berhasil didapatkan',
                'data' => $metodePembayaranQuery->get()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal mendapatkan metode pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment method based on request
     */
    protected function getMetodePembayaran(Request $request)
    {
        if (strtolower($request->jenis_pembayaran) == 'transfer') {
            $tipeTransfer = TipeTransfer::where('metode_transfer', $request->metode_transfer)
                ->where('jenis_transfer', $request->jenis_transfer)
                ->firstOrFail();
                
            return MetodePembayaran::where('id_tipe_transfer', $tipeTransfer->id_tipe_transfer)
                ->firstOrFail();
        }
        
        return MetodePembayaran::whereNull('id_tipe_transfer')->firstOrFail();
    }

    /**
     * Process penjualan details and update stock
     */
    protected function processPenjualanDetails($details, $penjualan, $tanggal)
    {
        foreach ($details as $detail) {
            if (isset($detail['id_produk'])) {
                $stok = self::validateStok($detail['id_produk'], $detail['jumlah_produk']);
                if (!$stok['cukup']) {
                    throw new \Exception('Stok tidak mencukupi untuk produk ID ' . $detail['id_produk']);
                }
            }

            $penjualan->penjualanDetail()->create($detail);

            if (!empty($detail['id_produk'])) {
                Stok::create([
                    'id_produk' => $detail['id_produk'],
                    'jumlah_stok' => $detail['jumlah_produk'],
                    'jenis_stok' => 'Out',
                    'jenis_transaksi' => $penjualan->id_penjualan,
                    'tanggal_stok' => $tanggal,
                ]);
            }
        }
    }


    public function returProduk(Request $request, $id_penjualan)
    {
        $request->validate([
            'jumlah_retur' => 'required|integer|min:1',
        ]);
        
        if (!isset($request['id_produk']) && !isset($request['nama_produk'])) {
            return response()->json([
                'success' => false,
                'message' => "Data produk harus diisi salah satu: id_produk atau nama_produk."
            ], 422);
        }

        DB::beginTransaction();

        try {
            $penjualan = Penjualan::with('penjualanDetail')->findOrFail($id_penjualan);

            if (isset($request->id_produk)) {
                $detail = $penjualan->penjualanDetail()
                    ->where('id_produk', $request->id_produk)
                    ->first();
            } else {
                $detail = $penjualan->penjualanDetail()
                    ->where('nama_produk', $request->nama_produk)
                    ->first();
            }

            if (!$detail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk dengan ID ' . $request->id_produk . ' tidak ditemukan dalam penjualan.'
                ], 404);
            }

            // if ($detail->status_retur) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Produk dengan ID ' . $request->id_produk . ' sudah diretur.'
            //     ], 422);
            // }

            if ($request->jumlah_retur > $detail->jumlah_produk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah retur melebihi jumlah penjualan produk.'
                ], 422);
            }

            // Tambah stok kembali
            if (isset($request->id_produk)) {
                Stok::create([
                    'id_produk' => $detail->id_produk,
                    'jumlah_stok' => $request->jumlah_retur,
                    'jenis_stok' => 'In',
                    'jenis_transaksi' => 'Return Produk ' . $id_penjualan,
                    'tanggal_stok' => Carbon::now(),
                ]);
            }

            // Update penjualan detail
            $detail->status_retur = true;
            $detail->jumlah_produk -= $request->jumlah_retur;
            $detail->save();

            if($detail->jumlah_produk == 0){
                $totalBaru = 0;
                $penjualan->total_harga = $totalBaru;
                $penjualan->save();
            }
            // Recalculate total harga penjualan
            $totalBaru = $penjualan->penjualanDetail()
                ->selectRaw('SUM(jumlah_produk * harga_jual) as total')
                ->value('total');

            $penjualan->total_harga = $totalBaru;
            if($penjualan->uangDiterima >= $penjualan->total_harga){
                $penjualan->status_penjualan = 'Lunas';
            }
            $penjualan->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Retur produk berhasil diproses.'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses retur produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function returProdukBulk(Request $request, $id_penjualan)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.jumlah_retur' => 'required|integer|min:1',
        ]);

        // Validate product details
        foreach ($request->items as $index) {
            if (!isset($index['id_produk']) && !isset($index['nama_produk'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Data produk di index ke-{$index} harus diisi salah satu: id_produk atau nama_produk."
                ], 422);
            }
        }
        
        DB::beginTransaction();

        try {
            $penjualan = Penjualan::with('penjualanDetail')->findOrFail($id_penjualan);

            foreach ($request->items as $item) {
                if (isset($item['id_produk'])) {
                    $detail = $penjualan->penjualanDetail()
                        ->where('id_produk', $item['id_produk'])
                        ->first();
                } else {
                    $detail = $penjualan->penjualanDetail()
                        ->where('nama_produk', $item['nama_produk'])
                        ->first();
                }

                if (!$detail) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Produk dengan ID ' . $item['id_produk'] . ' tidak ditemukan dalam penjualan.'
                    ], 404);
                }

                if ($detail->status_retur) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Produk dengan ID ' . $item['id_produk'] . ' sudah diretur.'
                    ], 422);
                }

                if ($item['jumlah_retur'] > $detail->jumlah_produk) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Jumlah retur melebihi jumlah penjualan produk untuk produk ID ' . $item['id_produk']
                    ], 422);
                }

                if (isset($item['id_produk'])) {
                    Stok::create([
                        'id_produk' => $detail->id_produk,
                        'jumlah_stok' => $item['jumlah_retur'],
                        'jenis_stok' => 'In',
                        'jenis_transaksi' => 'Return Produk ' . $id_penjualan,
                        'tanggal_stok' => Carbon::now(),
                    ]);
                }

                // Update penjualan detail
                $detail->status_retur = true;
                $detail->jumlah_produk -= $item['jumlah_retur'];
                $detail->save();
            }

            if($detail->jumlah_produk == 0){
                $totalBaru = 0;
                $penjualan->total_harga = $totalBaru;
                $penjualan->save();
            }
            // Recalculate total harga penjualan
            $totalBaru = $penjualan->penjualanDetail()
                ->selectRaw('SUM(jumlah_produk * harga_jual) as total')
                ->value('total');

            $penjualan->total_harga = $totalBaru;
            $penjualan->total_harga = $totalBaru;
            if($penjualan->uangDiterima >= $penjualan->total_harga){
                $penjualan->status_penjualan = 'Lunas';
            }
            $penjualan->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Retur produk berhasil diproses secara bulk.',
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses retur produk secara bulk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}