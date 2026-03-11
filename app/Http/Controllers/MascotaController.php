<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\Especie;
use App\Models\Sexo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MascotaController extends Controller
{
    public function index(Request $request)
    {
        $query = Mascota::query()->with('administrador');
        if ($request->filled('id_especie')) {
            $query->where('id_especie', $request->query('id_especie'));
        }
        if ($request->filled('id_sexo')) {
            $query->where('id_sexo', $request->query('id_sexo'));
        }
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
        // basic validation so we fail earlier instead of throwing a 500 when the
        // database rejects a missing foreign key. during initial development we
        // don't have authentication enabled, so default to the first admin user.
        $request->validate([
            'id_admin' => 'nullable|exists:usuarios,id_usuario',
        ]);

        // extract fields except imagen; file upload handled separately
        $data = $request->only([
            'nombre','especie','id_especie','raza','edad','sexo','id_sexo','descripcion','estado','fecha_publicacion','id_admin'
        ]);

        // if the client didn't supply an admin id, fall back to a sensible value
        if (empty($data['id_admin'])) {
            $data['id_admin'] = DB::table('usuarios')->where('id_rol', DB::table('roles')->where('nombre_rol', 'admin')->value('id_rol'))->value('id_usuario') ?? 1;
        }

        if (!$request->filled('fecha_publicacion')) {
            $data['fecha_publicacion'] = now()->toDateString();
        }
        if (!$request->filled('estado')) {
            $data['estado'] = 'disponible';
        }
        if (!empty($data['id_especie'])) {
            $e = Especie::find($data['id_especie']);
            if ($e) {
                $data['especie'] = $e->nombre;
            }
        } elseif (!empty($data['especie'])) {
            $name = trim($data['especie']);
            if ($name !== '') {
                $e = Especie::whereRaw('LOWER(nombre)=?', [strtolower($name)])->first();
                if (!$e) $e = Especie::create(['nombre' => $name, 'activo' => 1]);
                $data['id_especie'] = $e->id_especie;
                $data['especie'] = $e->nombre;
            }
        }
        if (!empty($data['id_sexo'])) {
            $s = Sexo::find($data['id_sexo']);
            if ($s) {
                $data['sexo'] = $s->nombre;
            }
        } elseif (!empty($data['sexo'])) {
            $name = trim($data['sexo']);
            if ($name !== '') {
                $s = Sexo::whereRaw('LOWER(nombre)=?', [strtolower($name)])->first();
                if (!$s) $s = Sexo::create(['nombre' => $name, 'activo' => 1]);
                $data['id_sexo'] = $s->id_sexo;
                $data['sexo'] = $s->nombre;
            }
        }
        $file = $request->file('imagen') ?? $request->file('image') ?? $request->file('foto');
        if ($file) {
            $path = $file->store('mascotas', 'public');
            $data['imagen'] = rtrim(env('APP_URL', 'http://localhost'), '/').Storage::url($path);
        } else {
            $imgStr = $request->input('imagen') ?? $request->input('image') ?? $request->input('foto');
            if (is_string($imgStr) && preg_match('/^data:image\\/([a-zA-Z0-9]+);base64,/', $imgStr, $m)) {
            $raw = substr($imgStr, strpos($imgStr, ',') + 1);
            $bin = base64_decode($raw);
            $ext = strtolower($m[1]) ?: 'png';
            $filename = 'mascotas/'.uniqid('', true).'.'.$ext;
            Storage::disk('public')->put($filename, $bin);
            $data['imagen'] = rtrim(env('APP_URL', 'http://localhost'), '/').Storage::url($filename);
            }
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

        $request->validate([
            'id_admin' => 'nullable|exists:usuarios,id_usuario',
        ]);

        $data = $request->only([
            'nombre','especie','id_especie','raza','edad','sexo','id_sexo','descripcion','estado','fecha_publicacion','id_admin'
        ]);

        if (!empty($data['id_especie'])) {
            $e = Especie::find($data['id_especie']);
            if ($e) {
                $data['especie'] = $e->nombre;
            }
        } elseif (!empty($data['especie'])) {
            $name = trim($data['especie']);
            if ($name !== '') {
                $e = Especie::whereRaw('LOWER(nombre)=?', [strtolower($name)])->first();
                if (!$e) $e = Especie::create(['nombre' => $name, 'activo' => 1]);
                $data['id_especie'] = $e->id_especie;
                $data['especie'] = $e->nombre;
            }
        }
        if (!empty($data['id_sexo'])) {
            $s = Sexo::find($data['id_sexo']);
            if ($s) {
                $data['sexo'] = $s->nombre;
            }
        } elseif (!empty($data['sexo'])) {
            $name = trim($data['sexo']);
            if ($name !== '') {
                $s = Sexo::whereRaw('LOWER(nombre)=?', [strtolower($name)])->first();
                if (!$s) $s = Sexo::create(['nombre' => $name, 'activo' => 1]);
                $data['id_sexo'] = $s->id_sexo;
                $data['sexo'] = $s->nombre;
            }
        }
        $file = $request->file('imagen') ?? $request->file('image') ?? $request->file('foto');
        if ($file) {
            $path = $file->store('mascotas', 'public');
            $data['imagen'] = rtrim(env('APP_URL', 'http://localhost'), '/').Storage::url($path);
        } else {
            $imgStr = $request->input('imagen') ?? $request->input('image') ?? $request->input('foto');
            if (is_string($imgStr) && preg_match('/^data:image\\/([a-zA-Z0-9]+);base64,/', $imgStr, $m)) {
            $raw = substr($imgStr, strpos($imgStr, ',') + 1);
            $bin = base64_decode($raw);
            $ext = strtolower($m[1]) ?: 'png';
            $filename = 'mascotas/'.uniqid('', true).'.'.$ext;
            Storage::disk('public')->put($filename, $bin);
            $data['imagen'] = rtrim(env('APP_URL', 'http://localhost'), '/').Storage::url($filename);
            }
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
        $rows = DB::table('especies')
            ->select('id_especie','nombre')
            ->where('activo', 1)
            ->orderBy('nombre')
            ->get();
        return response()->json($rows);
    }
    
    public function sexos(Request $request)
    {
        $rows = DB::table('sexos')
            ->select('id_sexo','nombre')
            ->where('activo', 1)
            ->orderBy('nombre')
            ->get();
        return response()->json($rows);
    }
}
