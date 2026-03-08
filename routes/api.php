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
Route::apiResource('disponibilidad-citas', DisponibilidadCitaController::class);
Route::post('citas/{id}/confirmar', [CitaController::class, 'confirmar']);
Route::post('citas/{id}/cancelar', [CitaController::class, 'cancelar']);
Route::get('admin/citas-activas', [CitaController::class, 'activasAhora']);
Route::get('admin/citas', [CitaController::class, 'adminIndex']);
Route::patch('citas/{id}/estado', [CitaController::class, 'cambiarEstado']);
Route::apiResource('veterinarios', VeterinarioController::class);
Route::apiResource('historias-clinicas', HistoriaClinicaController::class);
Route::apiResource('registros-clinicos', RegistroClinicoController::class);
Route::apiResource('solicitudes-adopcion', SolicitudAdopcionController::class);
Route::post('solicitudes-adopcion/{id}/aprobar', [SolicitudAdopcionController::class, 'aprobar']);
Route::post('solicitudes-adopcion/{id}/rechazar', [SolicitudAdopcionController::class, 'rechazar']);
Route::apiResource('metodos-pago', MetodoPagoController::class);
Route::apiResource('pagos', PagoController::class);
