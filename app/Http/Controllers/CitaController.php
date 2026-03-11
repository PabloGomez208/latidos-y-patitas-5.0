<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\DisponibilidadCita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CitaController extends Controller
{
    public function index()
    {
        $citas = Cita::with('cliente', 'disponibilidad.veterinario')->get();
        return response()->json($citas);
    }

    /**
     * Listado completo para panel de administración
     */
    public function adminIndex()
    {
        $citas = Cita::with([
            'cliente:id_usuario,nombre,email,telefono',
            'disponibilidad:id_disponibilidad,fecha,hora_inicio,hora_fin,id_veterinario',
            'disponibilidad.veterinario:id_veterinario,id_usuario'
        ])->get();

        return response()->json($citas);
    }

    public function porCliente($userId)
    {
        $estado = request()->query('estado');
        $q = Cita::with('disponibilidad.veterinario')
            ->where('id_cliente', $userId);
        if (!empty($estado)) {
            $q->where('estado', $estado);
        }
        $citas = $q->get();
        return response()->json($citas);
    }

    public function pendientesPorCliente($userId)
    {
        $citas = Cita::with('disponibilidad.veterinario')
            ->where('id_cliente', $userId)
            ->where('estado', 'pendiente')
            ->get();
        return response()->json($citas);
    }

    public function activasAhora()
    {
        $today = now()->toDateString();
        $time  = now()->toTimeString();

        $citas = Cita::with('cliente', 'disponibilidad.veterinario')
            ->whereHas('disponibilidad', function ($q) use ($today, $time) {
                $q->where('fecha', $today)
                  ->where('hora_inicio', '<=', $time)
                  ->where('hora_fin', '>=', $time);
            })
            ->get();

        return response()->json($citas);
    }

    public function store(Request $request)
    {
        // id_cliente puede omitirse, lo rellenamos automáticamente.
        $data = $request->validate([
            'id_cliente' => ['nullable','integer','exists:usuarios,id_usuario'],
            'id_disponibilidad' => ['required', 'integer', 'exists:disponibilidad_citas,id_disponibilidad'],
            'motivo' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'in:pendiente,confirmada,cancelada'],
        ]);
        if (empty($data['id_cliente'])) {
            // si hay usuario autenticado lo usamos (si se implementa auth)
            $user = $request->attributes->get('user');
            if ($user && $user->rol && $user->rol->nombre_rol === 'cliente') {
                $data['id_cliente'] = $user->id_usuario;
            } else {
                $clienteId = DB::table('usuarios')
                    ->whereIn('id_rol', function ($q) {
                        $q->select('id_rol')->from('roles')->where('nombre_rol', 'cliente');
                    })
                    ->value('id_usuario');
                if ($clienteId) {
                    $data['id_cliente'] = $clienteId;
                }
            }
        }
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

    /**
     * Obtiene las citas asociadas a un veterinario, opcionalmente filtradas por estado.
     */
    public function porVeterinario($vetId)
    {
        $estado = request()->query('estado');
        $q = Cita::with('cliente', 'disponibilidad.veterinario')
            ->whereHas('disponibilidad', fn ($q) => $q->where('id_veterinario', $vetId));
        if (!empty($estado)) {
            $q->where('estado', $estado);
        }
        $citas = $q->get();
        return response()->json($citas);
    }

    /**
     * Reservar mediante veterinario, fecha y hora (conveniencia cliente).
     */
    public function solicitar(Request $request)
    {
        $data = $request->validate([
            'id_cliente' => ['nullable','integer','exists:usuarios,id_usuario'],
            'id_veterinario' => ['required','integer','exists:veterinarios,id_veterinario'],
            'fecha' => ['required','date'],
            'hora' => ['required','date_format:H:i'],
            'motivo' => ['nullable','string','max:255'],
        ]);
        $disp = DisponibilidadCita::where('id_veterinario', $data['id_veterinario'])
            ->where('fecha', $data['fecha'])
            ->where('hora_inicio', '<=', $data['hora'])
            ->where('hora_fin', '>', $data['hora'])
            ->where('estado', 'disponible')
            ->first();
        if (!$disp) {
            return response()->json(['message' => 'No hay disponibilidad en ese horario'], 422);
        }
        $data['id_disponibilidad'] = $disp->id_disponibilidad;
        // mismo llenado de cliente que en store
        if (empty($data['id_cliente'])) {
            $user = $request->attributes->get('user');
            if ($user && $user->rol && $user->rol->nombre_rol === 'cliente') {
                $data['id_cliente'] = $user->id_usuario;
            } else {
                $clienteId = DB::table('usuarios')
                    ->whereIn('id_rol', function ($q) {
                        $q->select('id_rol')->from('roles')->where('nombre_rol', 'cliente');
                    })
                    ->value('id_usuario');
                if ($clienteId) {
                    $data['id_cliente'] = $clienteId;
                }
            }
        }
        $data['estado'] = 'pendiente';
        $cita = Cita::create($data);
        $disp->estado = 'reservada';
        $disp->save();
        return response()->json($cita, 201);
    }

    /**
     * Sólo las citas pendientes para un veterinario.
     */
    public function pendientesPorVeterinario($vetId)
    {
        $citas = Cita::with('cliente', 'disponibilidad.veterinario')
            ->where('estado', 'pendiente')
            ->whereHas('disponibilidad', fn ($q) => $q->where('id_veterinario', $vetId))
            ->get();
        return response()->json($citas);
    }

    // utilidad común para autorizar a un veterinario
    protected function authorizeVet($vetId, Cita $cita)
    {
        if ($cita->disponibilidad && $cita->disponibilidad->id_veterinario != $vetId) {
            abort(403, 'No autorizado para esta cita');
        }
    }

    public function confirmarPorVeterinario($vetId, $id)
    {
        $cita = Cita::with('disponibilidad')->findOrFail($id);
        $this->authorizeVet($vetId, $cita);
        $cita->estado = 'confirmada';
        $cita->save();
        return response()->json($cita);
    }

    public function cancelarPorVeterinario($vetId, $id)
    {
        $cita = Cita::with('disponibilidad')->findOrFail($id);
        $this->authorizeVet($vetId, $cita);
        $cita->estado = 'cancelada';
        $cita->save();
        return response()->json($cita);
    }

    public function update(Request $request, $id)
    {
        $cita = Cita::findOrFail($id);
        $cita->update($request->all());
        return response()->json($cita);
    }

    public function confirmar($id)
    {
        $cita = Cita::findOrFail($id);
        $cita->estado = 'confirmada';
        $cita->save();
        return response()->json($cita);
    }

    public function cancelar($id)
    {
        $cita = Cita::findOrFail($id);
        $cita->estado = 'cancelada';
        $cita->save();
        return response()->json($cita);
    }

    /**
     * Cambia el estado de la cita (pendiente|confirmada|cancelada)
     */
    public function cambiarEstado(Request $request, $id)
    {
        $estado = $request->input('estado');
        $permitidos = ['pendiente','confirmada','cancelada'];
        if (!in_array($estado, $permitidos, true)) {
            return response()->json(['message' => 'Estado inválido'], 422);
        }
        $cita = Cita::findOrFail($id);
        $cita->estado = $estado;
        $cita->save();
        return response()->json($cita);
    }

    public function destroy($id)
    {
        Cita::destroy($id);
        return response()->json(['message' => 'Cita eliminada']);
    }
}
