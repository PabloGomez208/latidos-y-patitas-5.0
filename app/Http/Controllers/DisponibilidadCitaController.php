<?php

namespace App\Http\Controllers;

use App\Models\DisponibilidadCita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DisponibilidadCitaController extends Controller
{
    public function index()
    {
        $disponibilidades = DisponibilidadCita::with('veterinario')->get();
        return response()->json($disponibilidades);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_veterinario' => ['nullable', 'integer'],
            'fecha' => ['required', 'date'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i'],
            'estado' => ['nullable', 'in:disponible,reservada'],
        ]);
        if (empty($data['id_veterinario'])) {
            $data['id_veterinario'] = DB::table('veterinarios')->value('id_veterinario');
        }
        $data['estado'] = $data['estado'] ?? 'disponible';
        $disponibilidad = DisponibilidadCita::create($data);
        return response()->json($disponibilidad, 201);
    }

    public function show($id)
    {
        $disponibilidad = DisponibilidadCita::with('veterinario')->findOrFail($id);
        return response()->json($disponibilidad);
    }

    public function update(Request $request, $id)
    {
        $disponibilidad = DisponibilidadCita::findOrFail($id);
        $disponibilidad->update($request->all());
        return response()->json($disponibilidad);
    }

    public function destroy($id)
    {
        DisponibilidadCita::destroy($id);
        return response()->json(['message' => 'Disponibilidad eliminada']);
    }
}
