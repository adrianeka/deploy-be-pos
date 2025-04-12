<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';
    protected $primaryKey = 'id_pembayaran';
    protected $fillable = ['tanggal_pembayaran', 'total_bayar', 'keterangan'];

    public function penjualan()
    {
        return $this->hasMany(Penjualan::class, 'id_penjualan');
    }

    public function pembelian()
    {
        return $this->hasMany(Pembelian::class, 'id_pembelian');
    }
}
