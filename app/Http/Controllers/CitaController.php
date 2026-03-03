<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\DisponibilidadCita;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    public function index()
    {
        $citas = Cita::with('cliente', 'disponibilidad.veterinario')->get();
        return response()->json($citas);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_cliente' => ['required', 'integer', 'exists:usuarios,id_usuario'],
            'id_disponibilidad' => ['required', 'integer', 'exists:disponibilidad_citas,id_disponibilidad'],
            'motivo' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'in:pendiente,confirmada,cancelada'],
        ]);
        $data['estado'] = $data['estado'] ?? 'pendiente';
        $cita = Cita::create($data);
        DisponibilidadCita::where('id_disponibilidad', $data['id_disponibilidad'])->update(['estado' => 'reservada']);
        return response()->json($cita, 201);
    }

    public function show($id)
    {
        $cita = Cita::with('cliente', 'disponibilidad.veterinario', 'pago')->findOrFail($id);
        return response()->json($cita);
    }

    public function update(Request $request, $id)
    {
        $cita = Cita::findOrFail($id);
        $cita->update($request->all());
        return response()->json($cita);
    }

    public function destroy($id)
    {
        Cita::destroy($id);
        return response()->json(['message' => 'Cita eliminada']);
    }
}
