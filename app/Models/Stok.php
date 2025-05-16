<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stok extends Model
{
    use HasFactory;

    protected $table = 'stok';
    protected $primaryKey = 'id_stok';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'id_produk',
        'jumlah_stok',
        'jenis_stok',
        'jenis_transaksi',
        'keterangan'
    ];

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }

    public function getNamaProdukAttribute()
    {
        return $this->produk?->nama_produk ?? '-';
    }

    public function stok(): BelongsTo
    {
        return $this->belongsTo(Stok::class, 'id_stok');
    }

    public static function getStokTersediaByProduk($id_produk)
    {
        return self::where('id_produk', $id_produk)
            ->get()
            ->sum(function ($stok) {
                if ($stok->jenis_stok === 'In') {
                    return $stok->jumlah_stok;
                } else {
                    return -$stok->jumlah_stok;
                }
            });
    }
}
