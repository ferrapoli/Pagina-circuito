<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marcador extends Model
{
    protected $fillable = ['titulo', 'descripcion', 'latitud', 'longitud', 'tipo', 'activo'];

    /**
     * Casts para que 'activo' sea tratado como booleano.
     */
    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Scope para obtener solo los marcadores activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
