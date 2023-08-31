<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha',
        'hora_inicio',
        'hora_final',
        'disponible',
        'id_docente',
    ];
    
}
