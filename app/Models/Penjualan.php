<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;

    protected $table = 'penjualan';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id_penjualan';
    protected $fillable = ['id_penjualan', 'id_kasir', 'id_pelanggan', 'id_bayar_zakat', 'tanggal_penjualan', 'total_harga', 'status_penjualan', 'status_retur', 'diskon'];

    public function kasir()
    {
        return $this->belongsTo(Kasir::class, 'id_kasir');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }

    public function penjualanDetail()
    {
        return $this->hasMany(PenjualanDetail::class, 'id_penjualan');
    }

    public function pembayaranPenjualan()
    {
        return $this->hasOne(PembayaranPenjualan::class, 'id_penjualan', 'id_penjualan');
    }

    public function pembayaran()
    {
        return $this->hasManyThrough(
            Pembayaran::class,
            PembayaranPenjualan::class,
            'id_penjualan', // Foreign key on PembayaranPenjualan table...
            'id_pembayaran', // Foreign key on Pembayaran table...
            'id_penjualan', // Local key on Penjualan table...
            'id_pembayaran' // Local key on PembayaranPenjualan table...
        );
    }

    protected function uangDiterima(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Mengambil total bayar hanya untuk id_penjualan yang sama
                return $this->pembayaran->sum('total_bayar') ?? 0;
            }
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
    protected function sisaPembayaran(): Attribute
    {
        return Attribute::make(
            get: function () {
                $totalHarga = $this->total_harga;
                $totalDiterima = $this->uang_diterima;

                if (in_array($this->status_penjualan, ['belum lunas', 'pesanan'])) {
                    $sisa = $totalHarga - $totalDiterima;
                    return max($sisa, 0);
                }

                return 0;
            }
        );
    }
}
