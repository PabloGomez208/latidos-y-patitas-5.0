<?php

namespace App\Http\Controllers;

use App\Models\SolicitudAdopcion;
use Illuminate\Http\Request;

class SolicitudAdopcionController extends Controller
{
    public function index()
    {
        // listado general (para administración o API pública)
        $solicitudes = SolicitudAdopcion::with('cliente', 'mascota')->get();
        return response()->json($solicitudes);
    }

    /**
     * Devuelve sólo solicitudes pendientes (útil para el panel de admin).
     */
    public function pendientes()
    {
        $solicitudes = SolicitudAdopcion::with('cliente', 'mascota')
            ->where('estado', 'pendiente')
            ->get();
        return response()->json($solicitudes);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_cliente' => ['required', 'integer'],
            'id_mascota' => ['required', 'integer'],
            'motivo' => ['required', 'string'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'tiene_mascotas' => ['nullable', 'boolean'],
            'experiencia' => ['nullable', 'string'],
            'estado' => ['nullable', 'in:pendiente,aprobada,rechazada'],
        ]);
        if (!$request->has('fecha_solicitud')) {
            $data['fecha_solicitud'] = now()->toDateString();
        }
        $data['estado'] = $data['estado'] ?? 'pendiente';
        $solicitud = SolicitudAdopcion::create($data);
        return response()->json($solicitud, 201);
    }

    public function show($id)
    {
        $solicitud = SolicitudAdopcion::with('cliente', 'mascota')->findOrFail($id);
        return response()->json($solicitud);
    }

    public function update(Request $request, $id)
    {
        $solicitud = SolicitudAdopcion::findOrFail($id);
        $data = $request->validate([
            'id_cliente' => ['sometimes', 'integer'],
            'id_mascota' => ['sometimes', 'integer'],
            'motivo' => ['sometimes', 'string'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'tiene_mascotas' => ['nullable', 'boolean'],
            'experiencia' => ['nullable', 'string'],
            'estado' => ['nullable', 'in:pendiente,aprobada,rechazada'],
            'aprobado_por' => ['nullable', 'integer'],
            'fecha_revision' => ['nullable', 'date'],
            'nota_revision' => ['nullable', 'string'],
        ]);
        $solicitud->update($data);
        return response()->json($solicitud);
    }

    public function adoptar(Request $request, $id)
    {
        $data = $request->validate([
            'id_cliente' => ['nullable', 'integer'],
            'motivo' => ['nullable', 'string'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'tiene_mascotas' => ['nullable', 'boolean'],
            'experiencia' => ['nullable', 'string'],
        ]);
        $data['id_mascota'] = (int) $id;
        if (empty($data['id_cliente'])) {
            $data['id_cliente'] = \Illuminate\Support\Facades\DB::table('usuarios')
                ->whereIn('id_rol', function ($q) {
                    $q->select('id_rol')->from('roles')->where('nombre_rol', 'cliente');
                })
                ->value('id_usuario');
        }
        $data['fecha_solicitud'] = now()->toDateString();
        $data['estado'] = 'pendiente';
        $solicitud = SolicitudAdopcion::create($data);
        return response()->json($solicitud, 201);
    }


    public function aprobar($id)
    {
        return $this->updateEstadoSimple($id, 'aprobada');
    }

    public function rechazar($id)
    {
        return $this->updateEstadoSimple($id, 'rechazada');
    }

    public function destroy($id)
    {
        SolicitudAdopcion::destroy($id);
        return response()->json(['message' => 'Solicitud de adopción eliminada']);
    }

    public function updateEstado(Request $request, $id)
    {
        $valor = $request->input('estado') ?? $request->input('accion');
        if (!$valor) {
            return response()->json(['message' => 'Estado requerido'], 422);
        }
        $valor = strtolower($valor);
        if (in_array($valor, ['aprobar','aprobada','aceptar','aceptada'])) {
            return $this->updateEstadoSimple($id, 'aprobada');
        }
        if (in_array($valor, ['rechazar','rechazada'])) {
            return $this->updateEstadoSimple($id, 'rechazada');
        }
        return response()->json(['message' => 'Estado inválido'], 422);
    }

    protected function updateEstadoSimple($id, $estado)
    {
        $solicitud = SolicitudAdopcion::with('mascota')->findOrFail($id);
        $solicitud->estado = $estado;
        $solicitud->fecha_revision = now()->toDateString();
        $solicitud->save();
        if ($estado === 'aprobada' && $solicitud->mascota) {
            $solicitud->mascota->estado = 'adoptada';
            $solicitud->mascota->save();
        }
        return response()->json($solicitud);
    }
}
