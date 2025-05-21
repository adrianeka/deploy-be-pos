<?php

namespace App\Models;

use App\Enums\StatusTransaksiPembelian;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    use HasFactory;

    protected $table = 'pembelian';
    protected $primaryKey = 'id_pembelian';
    protected $fillable = ['id_pemasok', 'total_harga', 'status_pembelian'];

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

    protected function uangBayar(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->pembayaran->sum('total_bayar') ?? 0;
            }
        );
    }

    protected function uangKembalian(): Attribute
    {
        return Attribute::make(
            get: fn () => max($this->uang_bayar - $this->total_harga, 0)
        );
    }
    protected function sisaPembayaran(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Konversi status ke string jika berupa enum
                $status = $this->status_pembelian instanceof \App\Enums\StatusTransaksiPembelian
                    ? $this->status_pembelian->value
                    : $this->status_pembelian;

                if (in_array($status, [StatusTransaksiPembelian::BelumLunas->value, 
                                    StatusTransaksiPembelian::Diproses->value])) {
                    $sisa = $this->total_harga - $this->uang_bayar;
                    return max($sisa, 0);
                }
                
                return 0;
            }
        );
    }
}
