<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produks';
    protected $primaryKey = 'id_produk';
    protected $fillable = [
        'nama_produk',
        'id_kategori',
        'gambar',
        'id_satuan',
        'harga_beli',
        'stok',
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

    public function levelHargas()
    {
        return $this->hasMany(LevelHarga::class, 'id_produk');
    }

    public function appliedLevelHarga()
    {
        return $this->hasOne(LevelHarga::class, 'id_produk')->where('is_applied', true);
    }
}
