<?php

namespace App\Observers;

use App\Models\PenjualanDetail;
use App\Models\User;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class PenjualanDetailObserver
{
    protected function notifyStokMinimum($produk, $user){
        try{
            Notification::make()
                ->title('Stok Minimum Tercapai')
                ->body('Stok produk "' . $produk->nama_produk . '" sudah mencapai stok minimum!')
                ->warning()
                ->sendToDatabase($user)
                ->broadcast($user);

        } catch (Exception $e) {
            Log::error('Error saat mengirim notifikasi: ' . $e->getMessage(), [
                'penjualan_id' => $produk->nama_produk ?? null,
                'user_id' => $user->id ?? null
            ]);
        }
    }
    public function created(PenjualanDetail $detail): void
    {
        try {
            // Load relasi yang diperlukan
            $penjualan = $detail->penjualan()->with('kasir')->first();
            
            if (!$penjualan?->kasir?->id_pemilik) {
                Log::warning("Data tidak lengkap untuk notifikasi stok", [
                    'detail_id' => $detail->id_penjualan_detail,
                    'penjualan_id' => $detail->id_penjualan
                ]);
                return;
            }
            
            $user = User::find($penjualan->kasir->id_pemilik);
            $produk = $detail->produk;
            
            if (!$user || !$produk) {
                Log::warning("User atau produk tidak ditemukan", [
                    'user_id' => $penjualan->kasir->id_pemilik,
                    'produk_id' => $detail->id_produk
                ]);
                return;
            }
            
            // Cek apakah perlu notifikasi stok minimum
            $stokTersedia = \App\Models\Stok::getStokTersediaByProduk($produk->id_produk);
            
            Log::info('Cek stok minimum', [
                'produk_id' => $produk->id_produk,
                'nama_produk' => $produk->nama_produk,
                'stok_tersedia' => $stokTersedia,
                'stok_minimum' => $produk->stok_minimum
            ]);
            
            if ($stokTersedia <= $produk->stok_minimum) {
                $this->notifyStokMinimum($produk, $user);               
            }
        } catch (Exception $e) {
            Log::error('Error di PenjualanDetailObserver: ' . $e->getMessage(), [
                'detail_id' => $detail->id_penjualan_detail ?? null,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle the Penjualan "updated" event.
     */
    public function updated(PenjualanDetail $detail): void
    {
        //
    }

    /**
     * Handle the Penjualan "deleted" event.
     */
    public function deleted(PenjualanDetail $detail): void
    {
        //
    }

    /**
     * Handle the Penjualan "restored" event.
     */
    public function restored(PenjualanDetail $detail): void
    {
        //
    }

    /**
     * Handle the Penjualan "force deleted" event.
     */
    public function forceDeleted(PenjualanDetail $detail): void
    {
        //
    }
}
