<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanDetail extends Model
{
    use HasFactory;

    protected $table = 'pembelian_detail';
    protected $primaryKey = 'id_pembelian_detail';
    protected $fillable = ['id_pembelian', 'id_produk', 'jumlah_produk'];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'id_pembelian');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }
}
