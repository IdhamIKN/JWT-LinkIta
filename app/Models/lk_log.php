<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class lk_log extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'id_pel', 'status', 'ket', 'signature', 'content', 'created_at', 'updated_at'
    ];


}
