<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stok extends Model
{
    use HasFactory;

    protected $table = 'stoks';
    protected $primaryKey = 'id_stok';
    public $timestamps = true;

    protected $fillable = [
        'id_stok',
        'id_produk',
        '  ',
        'alamat_toko',
        'jenis_usaha',
        'no_telp'
    ];
}
