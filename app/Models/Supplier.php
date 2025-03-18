<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'suppliers';
    protected $primaryKey = 'id_supplier';
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
