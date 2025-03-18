<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenerimaZakat extends Model
{
    use HasFactory;

    protected $table = 'penerima_zakats';
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

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_pemilik');
    }
}
