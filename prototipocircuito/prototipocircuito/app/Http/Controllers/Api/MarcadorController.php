<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Marcador;
use Illuminate\Http\Request;

class MarcadorController extends Controller
{
    /**
     * Devuelve todos los marcadores en JSON.
     */
    public function index()
    {
        return response()->json(Marcador::all());
    }

    /**
     * Valida y crea un nuevo marcador.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo'      => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'latitud'     => 'required|numeric',
            'longitud'    => 'required|numeric',
        ]);

        $marcador = Marcador::create($validated);

        return response()->json($marcador, 201);
    }

    /**
     * Elimina un marcador por su ID.
     */
    public function destroy($id)
    {
        $marcador = Marcador::findOrFail($id);
        $marcador->delete();

        return response()->json(['message' => 'Marcador eliminado'], 200);
    }
}
