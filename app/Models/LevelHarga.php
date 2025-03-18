<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LevelHarga extends Model
{
    use HasFactory;

    protected $table = 'level_hargas';
    protected $primaryKey = 'id_level_harga';
    protected $fillable = [
        'id_produk',
        'nama_level',
        'harga_jual',
    ];

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }
}
