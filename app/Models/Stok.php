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
    public $timestamps = true;

    protected $fillable = [
        'id_produk',
        'jumlah_stok',
        'jenis_stok',
        'jenis_transaksi',
        'tanggal_stok',
        'keterangan'
    ];

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }

    public function stok()
    {
        return $this->hasMany(Stok::class, 'id_produk');
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
