<?php

namespace App\Models;

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
}
