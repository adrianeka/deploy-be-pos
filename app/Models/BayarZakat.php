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
        'id_metode_pembayaran',
        'id_pemilik',
        'id_penerima_zakat',
        'modal_terjual',
        'nominal_zakat',
        'tanggal_bayar'
    ];

    public function penerima_zakat(): BelongsTo
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

    public function metode_pembayaran()
    {
        return $this->belongsTo(MetodePembayaran::class, 'id_metode_pembayaran');
    }

    protected function namaPenerima(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->penerima_zakat->nama_penerima;
            }
        );
    }
}
