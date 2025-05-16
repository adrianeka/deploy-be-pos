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

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class, 'id_tipe_transfer');
    }

    public static function getOpsiByMetodeTransfer(string $metode): array
    {
        return static::where('metode_transfer', $metode)
            ->pluck('jenis_transfer', 'id_tipe_transfer')
            ->toArray();
    }
}
