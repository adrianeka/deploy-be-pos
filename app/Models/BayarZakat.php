<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BayarZakat extends Model
{
    use HasFactory;

    protected $table = 'bayar_zakat';
    protected $primaryKey = 'id_bayar_zakat';

    protected $fillable = [
        'id_pemilik',
        'id_penerima_zakat',
        'id_tipe_transfer',
        'jenis_pembayaran',
        'modal_terjual',
        'nominal_zakat',
    ];

    public function penerimaZakat(): BelongsTo
    {
        return $this->belongsTo(PenerimaZakat::class, 'id_penerima_zakat', 'id_penerima_zakat');
    }

    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(Pemilik::class, 'id_pemilik', 'id_pemilik');
    }

    public function penjualan()
    {
        return $this->hasMany(Penjualan::class, 'id_bayar_zakat', 'id_bayar_zakat');
    }

    public function tipeTransfer(): BelongsTo
    {
        return $this->belongsTo(TipeTransfer::class, 'id_tipe_transfer', 'id_tipe_transfer');
    }
}
