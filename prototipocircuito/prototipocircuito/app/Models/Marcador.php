<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marcador extends Model
{
    protected $fillable = ['titulo', 'descripcion', 'latitud', 'longitud'];
}
