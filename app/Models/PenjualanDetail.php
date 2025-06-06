<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanDetail extends Model
{
    use HasFactory;

    protected $table = 'penjualan_detail';
    protected $primaryKey = 'id_penjualan_detail';
    protected $fillable = ['id_penjualan', 'id_produk', 'jumlah_produk', 'nama_produk', 'harga_jual', 'status_retur'];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'id_penjualan');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }

    public function levelHarga()
    {
        return $this->belongsTo(LevelHarga::class, 'id_level_harga');
    }
}
