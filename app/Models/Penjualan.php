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

    protected function uangDiterima(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->pembayaranPenjualan?->pembayaran?->sum('total_bayar') ?? 0;
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
}
