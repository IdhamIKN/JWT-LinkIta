<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tiket extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_user',
        'tiket',
        'status',
        'deposit'
    ];

    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'id_user');
    // }


}
