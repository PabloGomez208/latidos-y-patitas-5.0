<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $u = Usuario::where('email', $data['email'])->first();
        if (!$u || !Hash::check($data['password'], $u->password)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        $token = base64_encode('user:' . $u->id_usuario . '|' . now()->timestamp);

        return response()->json([
            'user' => [
                'id_usuario' => $u->id_usuario,
                'nombre' => $u->nombre,
                'email' => $u->email,
            ],
            'token' => $token,
        ], 200);
    }
}
