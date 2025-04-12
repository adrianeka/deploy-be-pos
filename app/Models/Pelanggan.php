<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'pelanggan';
    protected $primaryKey = 'id_pelanggan';
    protected $fillable = [
        'id_pemilik',
        'nama_pelanggan',
        'no_telp',
        'alamat',
    ];

    // public function produk(): BelongsTo
    // {
    //     return $this->belongsTo(Pemilik::class, 'id_pemilik');
    // }

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class, 'id_penjualan');
    }
}
