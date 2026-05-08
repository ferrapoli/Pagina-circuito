<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MapController extends Controller
{
    /**
     * Show the read-only map (existing functionality).
     */
    public function show()
    {
        $apiUrl  = 'https://xoqmqpkrbizlxpyrgpnp.supabase.co/rest/v1/rutas_geojson';
        $apiKey  = 'sb_publishable_U9YZzom_9mn1p4jy6kwBtQ_yd6Tzzgn';

        $response = Http::withoutVerifying()->withHeaders([
            'apikey'        => $apiKey,
            'Authorization' => 'Bearer ' . $apiKey,
        ])->get($apiUrl, [
            'select' => 'nombre,geojson',
            'nombre' => 'eq.rutas_montmelo',
        ]);

        $data = $response->json();

        if (empty($data) || !isset($data[0]['geojson'])) {
            abort(404, 'No se encontró la ruta.');
        }

        $ruta    = $data[0];
        $nombre  = $ruta['nombre'] ?? 'Ruta';
        $geojson = json_encode($ruta['geojson']);

        return view('map', [
            'nombre' => $nombre,
            'geojson' => $geojson,
        ]);
    }

    /**
     * Show the map editor with drawing tools.
     */
    public function editor()
    {
        // Load existing base GeoJSON from Supabase
        $apiUrl  = 'https://xoqmqpkrbizlxpyrgpnp.supabase.co/rest/v1/rutas_geojson';
        $apiKey  = 'sb_publishable_U9YZzom_9mn1p4jy6kwBtQ_yd6Tzzgn';

        $response = Http::withoutVerifying()->withHeaders([
            'apikey'        => $apiKey,
            'Authorization' => 'Bearer ' . $apiKey,
        ])->get($apiUrl, [
            'select' => 'nombre,geojson',
            'nombre' => 'eq.rutas_montmelo',
        ]);

        $data = $response->json();
        $baseGeojson = 'null';

        if (!empty($data) && isset($data[0]['geojson'])) {
            $baseGeojson = json_encode($data[0]['geojson']);
        }

        return view('map-editor', [
            'baseGeojson' => $baseGeojson,
        ]);
    }

    /**
     * Overwrite the existing GeoJSON with the newly edited features
     */
    public function saveMap(Request $request)
    {
        $validated = $request->validate([
            'geojson' => 'required|array',
        ]);

        $apiUrl  = 'https://xoqmqpkrbizlxpyrgpnp.supabase.co/rest/v1/rutas_geojson?nombre=eq.rutas_montmelo';
        $apiKey  = 'sb_publishable_U9YZzom_9mn1p4jy6kwBtQ_yd6Tzzgn';

        $response = Http::withoutVerifying()->withHeaders([
            'apikey'        => $apiKey,
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
            'Prefer'        => 'return=representation'
        ])->patch($apiUrl, [
            'geojson' => $validated['geojson']
        ]);

        if ($response->successful()) {
            return response()->json(['success' => true]);
        }

        return response()->json([
            'success' => false, 
            'error'   => $response->body()
        ], 500);
    }
}
