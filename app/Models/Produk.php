<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';
    protected $primaryKey = 'id_produk';
    protected $fillable = [
        'nama_produk',
        'id_kategori',
        'id_pemilik',
        'id_satuan',
        'id_stok',
        'foto_produk',
        'harga_beli',
        'stok_minimum',
        'deskripsi',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(Satuan::class, 'id_satuan');
    }

    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(Pemilik::class, 'id_pemilik');
    }

    public function level_hargas()
    {
        return $this->hasMany(LevelHarga::class, 'id_produk');
    }

    public function stok()
    {
        return $this->hasMany(Stok::class, 'id_stok');
    }

    public static function getStokProdukByPemilik($id_pemilik): Builder
    {
        return self::query()
            ->select('produk.*')
            ->where('produk.id_pemilik', $id_pemilik)
            ->whereIn('produk.id_produk', function ($subquery) {
                $subquery->select('id_produk')
                    ->from('stok')
                    ->distinct();
            });
    }
}
