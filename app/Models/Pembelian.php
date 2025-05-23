<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    use HasFactory;

    protected $table = 'pembelian';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id_pembelian';
    protected $fillable = ['id_pembelian', 'id_pemasok', 'total_harga', 'status_pembelian'];

    public function pemasok()
    {
        return $this->belongsTo(Pemasok::class, 'id_pemasok');
    }

    public function pembelianDetail()
    {
        return $this->hasMany(PembelianDetail::class, 'id_pembelian');
    }

    public function pembayaranPembelian()
    {
        return $this->hasMany(PembayaranPembelian::class, 'id_pembelian');
    }

    public function pembayaran()
    {
        return $this->hasManyThrough(
            Pembayaran::class,
            PembayaranPembelian::class,
            'id_pembelian',
            'id_pembayaran',
            'id_pembelian',
            'id_pembayaran'
        );
    }

    protected function uangDiterima(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->pembayaran?->sum('total_bayar') ?? 0,
        );
    }


    protected function uangKembalian(): Attribute
    {
        return Attribute::make(
            get: function () {
                $totalDiterima = $this->uang_diterima;
                $totalHarga = $this->total_harga;

                if ($totalDiterima > $totalHarga) {
                    return $totalDiterima - $totalHarga;
                }

                return 0;
            }
        );
    }

    protected function sisaBayar(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->uang_diterima < $this->total_harga
                ? $this->total_harga - $this->uang_diterima
                : 0,
        );
    }
}
