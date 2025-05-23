<?php

namespace App\Observers;

use App\Models\Pembayaran;
use App\Models\ArusKeuangan;

class PembayaranObserver
{
    /**
     * Handle the Pembayaran "created" event.
     */
    public function created(Pembayaran $pembayaran): void
    {
        //
    }

    /**
     * Handle the Pembayaran "updated" event.
     */
    public function updated(Pembayaran $pembayaran): void
    {
        ArusKeuangan::where('id_sumber', $pembayaran->id_pembayaran)->update([
            'nominal' => $pembayaran->total_bayar,
        ]);
    }

    /**
     * Handle the Pembayaran "deleted" event.
     */
    public function deleted(Pembayaran $pembayaran): void
    {
        //
    }

    /**
     * Handle the Pembayaran "restored" event.
     */
    public function restored(Pembayaran $pembayaran): void
    {
        //
    }

    /**
     * Handle the Pembayaran "force deleted" event.
     */
    public function forceDeleted(Pembayaran $pembayaran): void
    {
        //
    }
}
