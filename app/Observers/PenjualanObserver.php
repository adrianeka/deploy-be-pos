<?php

namespace App\Observers;

use App\Models\Penjualan;
use App\Models\User;
use App\Notifications\TransaksiPenjualanNotification;
use Filament\Notifications\Notification;

class PenjualanObserver
{
    /**
     * Handle the Penjualan "created" event.
     */
    public function created(Penjualan $penjualan): void
    {
        $pemilik = $penjualan->kasir?->id_pemilik;
        $user = User::findOrFail($pemilik);
        // $user->notify(new TransaksiPenjualanNotification($penjualan));
        Notification::make()
            ->title('Transaksi Baru: ' . $penjualan->id_penjualan)
            ->body('Total Rp ' . number_format($penjualan->total_harga))
            ->success()
            ->sendToDatabase($user);
            // ->sendToDatabase($user)
            // ->broadcast($user);
        }

    /**
     * Handle the Penjualan "updated" event.
     */
    public function updated(Penjualan $penjualan): void
    {
        //
    }

    /**
     * Handle the Penjualan "deleted" event.
     */
    public function deleted(Penjualan $penjualan): void
    {
        //
    }

    /**
     * Handle the Penjualan "restored" event.
     */
    public function restored(Penjualan $penjualan): void
    {
        //
    }

    /**
     * Handle the Penjualan "force deleted" event.
     */
    public function forceDeleted(Penjualan $penjualan): void
    {
        //
    }
}
