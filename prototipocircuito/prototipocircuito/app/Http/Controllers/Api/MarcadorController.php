<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Marcador;
use Illuminate\Http\Request;

class MarcadorController extends Controller
{
    /**
     * Devuelve todos los marcadores en JSON.
     * Si se pasa ?solo_activos=1, devuelve solo los activos (para la app pública).
     * Si se pasa ?tipo=4, filtra por tipo de marcador.
     */
    public function index(Request $request)
    {
        $query = Marcador::query();

        // Filtro: solo marcadores activos (para vista pública / Android)
        if ($request->boolean('solo_activos', false)) {
            $query->activos();
        }

        // Filtro: por tipo de marcador
        if ($request->has('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }

        return response()->json($query->get());
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
            'tipo'        => 'nullable|integer|min:1|max:7',
            'activo'      => 'nullable|boolean',
        ]);

        // Por defecto activo y tipo 1
        $validated['activo'] = $validated['activo'] ?? true;
        $validated['tipo']   = $validated['tipo'] ?? 1;

        $marcador = Marcador::create($validated);

        return response()->json($marcador, 201);
    }

    /**
     * Actualiza un marcador (principalmente para toggle activo/inactivo).
     */
    public function update(Request $request, $id)
    {
        $marcador = Marcador::findOrFail($id);

        $validated = $request->validate([
            'titulo'      => 'sometimes|string|max:255',
            'descripcion' => 'sometimes|nullable|string|max:255',
            'latitud'     => 'sometimes|numeric',
            'longitud'    => 'sometimes|numeric',
            'tipo'        => 'sometimes|integer|min:1|max:7',
            'activo'      => 'sometimes|boolean',
        ]);

        $marcador->update($validated);

        return response()->json($marcador);
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
