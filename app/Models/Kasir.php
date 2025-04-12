<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kasir extends Model
{
    use HasFactory;

    protected $table = 'kasir';
    protected $primaryKey = 'id_kasir';
    public $timestamps = true;

    protected $fillable = [
        'id_user',
        'id_pemilik',
        'nama',
        'no_telp',
        'alamat'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function pemilik()
    {
        return $this->belongsTo(Pemilik::class, 'id_pemilik');
    }

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class, 'id_penjualan');
    }
}
