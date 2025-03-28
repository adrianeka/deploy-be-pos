<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    public function getNamaProdukAttribute()
    {
        return $this->produk?->nama_produk ?? '-';
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

    // Scope untuk searching stok tersedia
    public function scopeSearchStokTersedia(Builder $query, string $search): Builder
    {
        return $query->whereHas('produk', function ($q) use ($search) {
            $q->whereRaw('(SELECT COALESCE(SUM(CASE WHEN jenis_stok = "In" THEN jumlah_stok ELSE -jumlah_stok END), 0) 
                 FROM stoks 
                 WHERE stoks.id_produk = produks.id_produk) LIKE ?', ["%{$search}%"]);
        });
    }

    // Scope untuk sorting stok tersedia
    public function scopeSortStokTersedia(Builder $query, string $direction): Builder
    {
        return $query->orderByRaw('(SELECT COALESCE(SUM(CASE WHEN jenis_stok = "In" THEN jumlah_stok ELSE -jumlah_stok END), 0) 
             FROM stoks 
             WHERE stoks.id_produk = produks.id_produk) ' . $direction);
    }
}
