<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Puntuacion extends Model
{
    use HasFactory;

    protected $fillable = [
        "id_pregunta",
        "id_reactivo",
        "asignado"
    ];
}
