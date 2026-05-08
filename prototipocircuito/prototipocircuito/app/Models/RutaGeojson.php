<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RutaGeojson extends Model
{
    protected $table = 'rutas_geojson';

    protected $fillable = ['nombre', 'geojson'];

    protected $casts = [
        'geojson' => 'array',
    ];
}
