<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Camino extends Model
{
    protected $table = 'caminos';

    protected $fillable = ['nombre', 'geojson', 'estado', 'color'];

    protected $casts = [
        'geojson' => 'array',
    ];
}
