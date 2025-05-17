<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\StatusTransaksiPenjualan;

class Penjualan extends Model
{
    use HasFactory;

    protected $table = 'penjualan';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id_penjualan';
    protected $fillable = ['id_penjualan', 'id_kasir', 'id_pelanggan', 'id_bayar_zakat',  'total_harga', 'status_penjualan', 'status_retur', 'diskon'];

    protected $casts = [
        'status_penjualan' => StatusTransaksiPenjualan::class,
    ];

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
            'id_penjualan',
            'id_pembayaran',
            'id_penjualan',
            'id_pembayaran'
        );
    }

    public function bayarZakat()
    {
        return $this->belongsTo(BayarZakat::class, 'id_bayar_zakat');
    }

    protected function uangDiterima(): Attribute
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

    public function calculateTotal()
    {
        $total = 0;
        foreach ($this->penjualanDetail as $detail) {
            $total += $detail->harga_jual * $detail->jumlah_produk;
        }
        return $total;
    }

    protected function modalTerjual(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->penjualanDetail->sum(function ($detail) {
                    return optional($detail->produk)->harga_beli * $detail->jumlah_produk;
                });
            }
        );
    }

    protected function zakat(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->modalTerjual * 0.025;
            }
        );
    }
}
