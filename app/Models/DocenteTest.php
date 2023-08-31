<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocenteTest extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_docente',
        'id_test'
    ];
}
