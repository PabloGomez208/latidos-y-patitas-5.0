<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VeterinarioController;
use App\Http\Controllers\MascotaController;
use App\Http\Controllers\DisponibilidadCitaController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\HistoriaClinicaController;
use App\Http\Controllers\RegistroClinicoController;
use App\Http\Controllers\SolicitudAdopcionController;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ContactoMensajeController;

// Seguridad desactivada: todas las rutas abiertas mientras terminas el frontend
Route::apiResource('roles', RolController::class);
Route::apiResource('usuarios', UsuarioController::class);
Route::apiResource('registro', RegistroController::class)->only(['store']);
Route::post('register', [RegistroController::class, 'store']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);

// Recursos abiertos
Route::apiResource('mascotas', MascotaController::class);
Route::get('mascotas/especies', [MascotaController::class, 'especies']);
Route::get('sexos', [MascotaController::class, 'sexos']);
Route::get('especies', [MascotaController::class, 'especies']);
Route::apiResource('disponibilidad-citas', DisponibilidadCitaController::class);
Route::patch('disponibilidad-citas/{id}/estado', [DisponibilidadCitaController::class, 'cambiarEstado']);
Route::apiResource('citas', CitaController::class);
Route::post('citas/solicitar', [CitaController::class, 'solicitar']);
Route::post('citas/{id}/confirmar', [CitaController::class, 'confirmar']);
Route::post('citas/{id}/cancelar', [CitaController::class, 'cancelar']);
Route::get('usuarios/{userId}/citas', [CitaController::class, 'porCliente']);
Route::get('usuarios/{userId}/citas/pendientes', [CitaController::class, 'pendientesPorCliente']);
Route::get('veterinarios/{vetId}/citas', [CitaController::class, 'porVeterinario']);
Route::get('veterinarios/{vetId}/citas/pendientes', [CitaController::class, 'pendientesPorVeterinario']);
Route::post('veterinarios/{vetId}/citas/{id}/confirmar', [CitaController::class, 'confirmarPorVeterinario']);
Route::post('veterinarios/{vetId}/citas/{id}/cancelar', [CitaController::class, 'cancelarPorVeterinario']);
Route::get('admin/citas-activas', [CitaController::class, 'activasAhora']);
Route::get('admin/citas', [CitaController::class, 'adminIndex']);
Route::patch('citas/{id}/estado', [CitaController::class, 'cambiarEstado']);
Route::apiResource('veterinarios', VeterinarioController::class);
Route::apiResource('historias-clinicas', HistoriaClinicaController::class);
Route::apiResource('registros-clinicos', RegistroClinicoController::class);
Route::apiResource('solicitudes-adopcion', SolicitudAdopcionController::class);
Route::get('admin/solicitudes-adopcion/pendientes', [SolicitudAdopcionController::class, 'pendientes']);
Route::post('mascotas/{id}/adoptar', [SolicitudAdopcionController::class, 'adoptar']);
Route::post('solicitudes-adopcion/{id}/aprobar', [SolicitudAdopcionController::class, 'aprobar']);
Route::post('solicitudes-adopcion/{id}/rechazar', [SolicitudAdopcionController::class, 'rechazar']);
Route::patch('solicitudes-adopcion/{id}/estado', [SolicitudAdopcionController::class, 'updateEstado']);
Route::apiResource('metodos-pago', MetodoPagoController::class);
Route::apiResource('pagos', PagoController::class);
// Contacto
Route::get('admin/contacto-mensajes', [ContactoMensajeController::class, 'index']);
Route::post('contacto', [ContactoMensajeController::class, 'store']);
Route::patch('admin/contacto-mensajes/{id}/estado', [ContactoMensajeController::class, 'updateEstado']);
