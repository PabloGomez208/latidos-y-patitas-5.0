<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MascotaController extends Controller
{
    public function index(Request $request)
    {
        $query = Mascota::query()->with('administrador');
        if ($request->filled('especie')) {
            $especie = strtolower($request->query('especie'));
            $query->whereRaw('LOWER(especie) = ?', [$especie]);
            $query->where('estado', $request->query('estado', 'disponible'));
        } elseif ($request->query('estado')) {
            $query->where('estado', $request->query('estado'));
        }
        $mascotas = $query->get();
        return response()->json($mascotas);
    }

    public function store(Request $request)
    {
        $mascota = Mascota::create($request->all());
        return response()->json($mascota, 201);
    }

    public function show($id)
    {
        $mascota = Mascota::with('administrador', 'historiaClinica')->findOrFail($id);
        return response()->json($mascota);
    }

    public function update(Request $request, $id)
    {
        $mascota = Mascota::findOrFail($id);
        $mascota->update($request->all());
        return response()->json($mascota);
    }

    public function destroy($id)
    {
        Mascota::destroy($id);
        return response()->json(['message' => 'Mascota eliminada']);
    }

    public function especies(Request $request)
    {
        $estado = $request->query('estado', 'disponible');
        $rows = DB::table('mascotas')
            ->select(DB::raw('DISTINCT especie'))
            ->whereNotNull('especie')
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->orderBy('especie')
            ->pluck('especie')
            ->filter()
            ->values();
        return response()->json($rows);
    }
}
