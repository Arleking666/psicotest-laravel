<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EscalaDimension extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_escala',
        'id_dimension'
    ];
}
