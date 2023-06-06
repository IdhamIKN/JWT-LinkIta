<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class mutasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_user',
        'id_transaksi',
        'jenis_transaksi',
        'status',
        'tanggal',
        'debit',
        'kredit', //deposit
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function transaksi()
    {
        return $this->belongsTo(LkLog::class, 'id_transaksi');
    }
}
