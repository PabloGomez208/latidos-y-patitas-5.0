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
        $data = $request->only([
            'nombre','especie','raza','edad','sexo','descripcion','estado','fecha_publicacion','id_admin','imagen'
        ]);
        if (!$request->filled('fecha_publicacion')) {
            $data['fecha_publicacion'] = now()->toDateString();
        }
        if (!$request->filled('estado')) {
            $data['estado'] = 'disponible';
        }
        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('mascotas', 'public');
            $data['imagen'] = rtrim(env('APP_URL', 'http://localhost'), '/').'/storage/'.$path;
        }
        $mascota = Mascota::create($data);
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
        $data = $request->only([
            'nombre','especie','raza','edad','sexo','descripcion','estado','fecha_publicacion','id_admin','imagen'
        ]);
        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('mascotas', 'public');
            $data['imagen'] = rtrim(env('APP_URL', 'http://localhost'), '/').'/storage/'.$path;
        }
        $mascota->update($data);
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
