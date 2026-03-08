<?php

namespace App\Http\Controllers;

use App\Models\ContactoMensaje;
use Illuminate\Http\Request;

class ContactoMensajeController extends Controller
{
    public function index()
    {
        $mensajes = ContactoMensaje::orderBy('fecha_envio', 'desc')->get();
        return response()->json($mensajes);
    }
    
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required','string','max:100'],
            'email' => ['required','email','max:150'],
            'asunto' => ['nullable','string','max:150'],
            'mensaje' => ['required','string'],
        ]);
        $data['fecha_envio'] = now();
        $data['estado'] = 'nuevo';
        $msg = ContactoMensaje::create($data);
        return response()->json($msg, 201);
    }
    
    public function updateEstado(Request $request, $id)
    {
        $estado = $request->input('estado');
        if (!in_array($estado, ['nuevo','leido','archivado'], true)) {
            return response()->json(['message' => 'Estado inválido'], 422);
        }
        $msg = ContactoMensaje::findOrFail($id);
        $msg->estado = $estado;
        $msg->save();
        return response()->json($msg);
    }
}
