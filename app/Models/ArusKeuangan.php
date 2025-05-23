<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArusKeuangan extends Model
{
    protected $table = 'arus_keuangan';
    protected $primaryKey = 'id_arus_keuangan';

    protected $fillable = [
        'id_pemilik',
        'id_sumber',
        'keterangan',
        'jenis_transaksi',
        'nominal',
    ];

    public function getNominalDebitAttribute()
    {
        return $this->jenis_transaksi === 'debit' ? $this->nominal : '-';
    }

    public function getNominalKreditAttribute()
    {
        if ($this->jenis_transaksi === 'kredit' && !is_null($this->nominal)) {
            return abs($this->nominal);
        }
        return '-';
    }

    public function pembayaran(): BelongsTo
    {
        return $this->belongsTo(Pembayaran::class, 'id_sumber', 'id_pembayaran');
    }

    /**
     * Calculate running balance based on tab filter
     */
    public function calculateRunningBalance($tabFilter = 'Semua', $dateFrom = null, $dateUntil = null)
    {
        $query = self::query()
            ->where('created_at', '<=', $this->created_at)
            ->where(function ($q) {
                $q->where('created_at', '<', $this->created_at)
                    ->orWhere('id_arus_keuangan', '<=', $this->id_arus_keuangan);
            });

        // Apply date range filter
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateUntil) {
            $query->whereDate('created_at', '<=', $dateUntil);
        }

        // Apply filter based on tab
        if ($tabFilter === 'Tunai') {
            $query->whereHas('pembayaran', function ($q) {
                $q->where('jenis_pembayaran', 'tunai');
            });
        } elseif ($tabFilter === 'Transfer') {
            $query->whereHas('pembayaran', function ($q) {
                $q->where('jenis_pembayaran', 'transfer');
            });
        }
        // For 'Semua' tab, no additional filter needed

        $transactions = $query->orderBy('created_at', 'asc')
            ->orderBy('id_arus_keuangan', 'asc')
            ->get();

        $balance = 0;
        foreach ($transactions as $transaction) {
            if ($transaction->jenis_transaksi === 'kredit') {
                $balance -= abs($transaction->nominal);
            } elseif ($transaction->jenis_transaksi === 'debit') {
                $balance += abs($transaction->nominal);
            }
        }

        return $balance;
    }

    /**
     * Get saldo attribute (for backward compatibility)
     */
    public function getSaldoAttribute()
    {
        return $this->calculateRunningBalance('Semua');
    }
}
