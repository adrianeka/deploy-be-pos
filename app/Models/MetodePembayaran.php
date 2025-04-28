<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MetodePembayaran extends Model
{
    use HasFactory;

    protected $table = 'metode_pembayaran';
    protected $primaryKey = 'id_metode_pembayaran';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'jenis_pembayaran',
        'id_tipe_transfer',
    ];

    public function tipe_transfer()
    {
        return $this->belongsTo(TipeTransfer::class, 'id_tipe_transfer');
    }
}