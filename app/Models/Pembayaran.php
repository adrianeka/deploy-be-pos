<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';
    protected $primaryKey = 'id_pembayaran';
    protected $fillable = ['id_pembayaran', 'total_bayar', 'keterangan', 'jenis_pembayaran', 'id_tipe_transfer'];

    public function tipeTransfer()
    {
        return $this->hasOne(TipeTransfer::class, 'id_tipe_transfer');
    }
}
