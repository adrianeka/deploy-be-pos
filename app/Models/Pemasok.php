<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pemasok extends Model
{
    use HasFactory;

    protected $table = 'pemasok';
    protected $primaryKey = 'id_pemasok';
    protected $fillable = [
        'id_pemilik',
        'nama_perusahaan',
        'no_telp',
        'alamat',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Pemilik::class, 'id_pemilik');
    }
}
