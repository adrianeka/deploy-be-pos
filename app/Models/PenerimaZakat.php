<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenerimaZakat extends Model
{
    use HasFactory;

    protected $table = 'penerima_zakat';
    protected $primaryKey = 'id_penerima_zakat';
    protected $fillable = [
        'id_penerima_zakat',
        'id_pemilik',
        'nama_penerima',
        'no_telp',
        'no_rekening',
        'nama_bank',
        'rekening_atas_nama',
        'alamat',
    ];

    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(Pemilik::class, 'id_pemilik', 'id_pemilik');
    }

    public function bayarZakat(): HasMany
    {
        return $this->hasMany(BayarZakat::class, 'id_penerima_zakat', 'id_penerima_zakat');
    }
}
