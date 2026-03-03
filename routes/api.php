<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VeterinarioController;
use App\Http\Controllers\MascotaController;
use App\Http\Controllers\DisponibilidadCitaController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\HistoriaClinicaController;
use App\Http\Controllers\RegistroClinicoController;
use App\Http\Controllers\SolicitudAdopcionController;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\AuthController;

Route::apiResource('roles', RolController::class);
Route::apiResource('usuarios', UsuarioController::class);
Route::apiResource('veterinarios', VeterinarioController::class);
Route::apiResource('mascotas', MascotaController::class);
Route::apiResource('disponibilidad-citas', DisponibilidadCitaController::class);
Route::apiResource('citas', CitaController::class);
Route::apiResource('historias-clinicas', HistoriaClinicaController::class);
Route::apiResource('registros-clinicos', RegistroClinicoController::class);
Route::apiResource('solicitudes-adopcion', SolicitudAdopcionController::class);
Route::apiResource('metodos-pago', MetodoPagoController::class);
Route::apiResource('pagos', PagoController::class);
Route::post('login', [AuthController::class, 'login']);
