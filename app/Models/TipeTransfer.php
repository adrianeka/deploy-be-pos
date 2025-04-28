<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipeTransfer extends Model
{
    use HasFactory;

    protected $table = 'tipe_transfer';
    protected $primaryKey = 'id_tipe_transfer';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'metode_transfer',
        'jenis_transfer',
    ];

    public function metode_pembayarans()
    {
        return $this->hasMany(MetodePembayaran::class, 'id_tipe_transfer');
    }
}
