<?php

namespace App\Observers;

use App\Models\Stok;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Exception;

class StokObserver
{
    /**
     * Handle the Stok "created" event.
     */
    public function created(Stok $stok): void
    {
        Log::info("MASUK OBSERVER STOK");
        try {
            // Hanya cek untuk stok keluar (penjualan)
            if (strtolower($stok->jenis_stok) !== 'out') {
                return;
            }
            
            $produk = $stok->produk;
            if (!$produk) {
                Log::warning("Produk tidak ditemukan", ['stok_id' => $stok->id_stok]);
                return;
            }
            
            // Cek stok tersedia
            $stokTersedia = Stok::getStokTersediaByProduk($produk->id_produk);
            Log::info("Stok Tersedia : " . $stokTersedia . "Stok yang di input : " . $stok->jumlah_stok);
            
            // Jika stok di bawah atau sama dengan stok minimum
            if ($stokTersedia <= $produk->stok_minimum) {
                // Cari user pemilik (lewat penjualan)
                $idPenjualan = $stok->jenis_transaksi;
                $penjualan = \App\Models\Penjualan::with('kasir')->find($idPenjualan);
                
                if (!$penjualan || !$penjualan->kasir || !$penjualan->kasir->id_pemilik) {
                    Log::warning("Data pemilik tidak ditemukan", [
                        'id_penjualan' => $idPenjualan
                    ]);
                    return;
                }
                
                $user = User::find($penjualan->kasir->id_pemilik);
                if (!$user) {
                    Log::warning("User tidak ditemukan", [
                        'id_pemilik' => $penjualan->kasir->id_pemilik
                    ]);
                    return;
                }
                
                $this->notifyStokMinimum($produk, $user, $stokTersedia);
            }
        } catch (Exception $e) {
            Log::error("Error di StokObserver: " . $e->getMessage(), [
                'stok_id' => $stok->id_stok ?? null,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function notifyStokMinimum($produk, $user, $stokTersedia): void
    {
        try {
            Notification::make()
                ->title('Stok Minimum Tercapai')
                ->body("Stok produk \"{$produk->nama_produk}\" tersisa {$stokTersedia} dari minimum {$produk->stok_minimum}!")
                ->warning()
                ->sendToDatabase($user)
                ->broadcast($user);
                
            Log::info("Notifikasi stok minimum terkirim", [
                'produk' => $produk->nama_produk,
                'stok_tersedia' => $stokTersedia,
                'stok_minimum' => $produk->stok_minimum
            ]);
        } catch (Exception $e) {
            Log::error("Error saat mengirim notifikasi stok: " . $e->getMessage(), [
                'produk_id' => $produk->id_produk ?? null,
                'user_id' => $user->id ?? null
            ]);
        }
    }
}