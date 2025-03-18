<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Satuan extends Model
{
    use HasFactory;

    protected $table = 'satuans';
    protected $primaryKey = 'id_satuan';
    protected $fillable = [
        'nama_satuan',
        'id_pemilik',
    ];

    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_pemilik');
    }
}
