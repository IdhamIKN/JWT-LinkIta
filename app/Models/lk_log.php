<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class lk_log extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'id_user',
        'id_pel',
        'nama',
        'method',
        'id_pay',
        'id_inq',
        'nominal',
        'status',
        'ket',
        'content',
        'created_at',
        'updated_at'
    ];


}
