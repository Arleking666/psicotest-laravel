<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recuperar extends Model
{
    use HasFactory;

    protected $fillable = [
        "id_usuario",
        "codigo",
        "estado"
    ];
}
