<?php

namespace App\Models;

use Filament\Forms\Components\Builder;
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

    public function scopeMilikUser(Builder $query, $userId): Builder
    {
        return $query->where('id_pemilik', $userId);
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Pemilik::class, 'id_pemilik');
    }

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class, 'id_pembelian');
    }
}
