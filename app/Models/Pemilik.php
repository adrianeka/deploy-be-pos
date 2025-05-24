<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pemilik extends Model
{
    use HasFactory;

    protected $table = 'pemilik';
    protected $primaryKey = 'id_pemilik';
    public $timestamps = true;

    protected $fillable = [
        'id_user',
        'nama_pemilik',
        'nama_perusahaan',
        'alamat_toko',
        'jenis_usaha',
        'no_telp'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function kasir()
    {
        return $this->hasMany(Kasir::class, 'id_pemilik');
    }
}
