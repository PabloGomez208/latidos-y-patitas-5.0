<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (!DB::table('roles')->where('nombre_rol', 'admin')->exists()) {
            DB::table('roles')->insert(['nombre_rol' => 'admin']);
        }
        if (!DB::table('roles')->where('nombre_rol', 'veterinario')->exists()) {
            DB::table('roles')->insert(['nombre_rol' => 'veterinario']);
        }
        if (!DB::table('roles')->where('nombre_rol', 'cliente')->exists()) {
            DB::table('roles')->insert(['nombre_rol' => 'cliente']);
        }

        if (!DB::table('metodos_pago')->where('nombre', 'Efectivo')->exists()) {
            DB::table('metodos_pago')->insert(['nombre' => 'Efectivo']);
        }
        if (!DB::table('metodos_pago')->where('nombre', 'Tarjeta')->exists()) {
            DB::table('metodos_pago')->insert(['nombre' => 'Tarjeta']);
        }
        if (!DB::table('metodos_pago')->where('nombre', 'PSE')->exists()) {
            DB::table('metodos_pago')->insert(['nombre' => 'PSE']);
        }
        if (!DB::table('metodos_pago')->where('nombre', 'Nequi')->exists()) {
            DB::table('metodos_pago')->insert(['nombre' => 'Nequi']);
        }
        if (!DB::table('metodos_pago')->where('nombre', 'Daviplata')->exists()) {
            DB::table('metodos_pago')->insert(['nombre' => 'Daviplata']);
        }

        // Asegurar usuario admin para referenciar en mascotas
        $adminRolId = DB::table('roles')->where('nombre_rol', 'admin')->value('id_rol');
        $adminUser = DB::table('usuarios')->where('email', 'admin@lyp.local')->first();
        if (!$adminUser) {
            DB::table('usuarios')->insert([
                'nombre' => 'Administrador',
                'email' => 'admin@lyp.local',
                'password' => Hash::make('admin123'),
                'telefono' => '0000000000',
                'id_rol' => $adminRolId,
            ]);
            $adminUser = DB::table('usuarios')->where('email', 'admin@lyp.local')->first();
        }

        // Insertar mascotas base: Perro y Gato
        $adminId = $adminUser->id_usuario ?? 1;
        $today = date('Y-m-d');
        if (!DB::table('mascotas')->where('especie', 'Perro')->exists()) {
            DB::table('mascotas')->insert([
                'nombre' => 'Perro',
                'especie' => 'Perro',
                'raza' => 'N/A',
                'edad' => 0,
                'sexo' => 'N/A',
                'descripcion' => 'Mascota genérica Perro para selección en adopción',
                'estado' => 'disponible',
                'fecha_publicacion' => $today,
                'id_admin' => $adminId,
            ]);
        }
        if (!DB::table('mascotas')->where('especie', 'Gato')->exists()) {
            DB::table('mascotas')->insert([
                'nombre' => 'Gato',
                'especie' => 'Gato',
                'raza' => 'N/A',
                'edad' => 0,
                'sexo' => 'N/A',
                'descripcion' => 'Mascota genérica Gato para selección en adopción',
                'estado' => 'disponible',
                'fecha_publicacion' => $today,
                'id_admin' => $adminId,
            ]);
        }

        $vetRolId = DB::table('roles')->where('nombre_rol', 'veterinario')->value('id_rol');
        $vetUser = DB::table('usuarios')->where('email', 'vet@lyp.local')->first();
        if (!$vetUser) {
            DB::table('usuarios')->insert([
                'nombre' => 'Veterinario',
                'email' => 'vet@lyp.local',
                'password' => Hash::make('vet123'),
                'telefono' => '0000000001',
                'id_rol' => $vetRolId,
            ]);
            $vetUser = DB::table('usuarios')->where('email', 'vet@lyp.local')->first();
        }
        $vetId = DB::table('veterinarios')->where('id_usuario', $vetUser->id_usuario)->value('id_veterinario');
        if (!$vetId) {
            DB::table('veterinarios')->insert([
                'id_usuario' => $vetUser->id_usuario,
                'especialidad' => 'General',
            ]);
            $vetId = DB::table('veterinarios')->where('id_usuario', $vetUser->id_usuario)->value('id_veterinario');
        }

        $slotDates = [
            date('Y-m-d', strtotime('+1 day')),
            date('Y-m-d', strtotime('+2 day')),
            date('Y-m-d', strtotime('+3 day')),
        ];
        foreach ($slotDates as $d) {
            $exists = DB::table('disponibilidad_citas')->where('id_veterinario', $vetId)->where('fecha', $d)->where('hora_inicio', '09:00:00')->exists();
            if (!$exists) {
                DB::table('disponibilidad_citas')->insert([
                    'id_veterinario' => $vetId,
                    'fecha' => $d,
                    'hora_inicio' => '09:00:00',
                    'hora_fin' => '10:00:00',
                    'estado' => 'disponible',
                ]);
            }
            $exists2 = DB::table('disponibilidad_citas')->where('id_veterinario', $vetId)->where('fecha', $d)->where('hora_inicio', '10:00:00')->exists();
            if (!$exists2) {
                DB::table('disponibilidad_citas')->insert([
                    'id_veterinario' => $vetId,
                    'fecha' => $d,
                    'hora_inicio' => '10:00:00',
                    'hora_fin' => '11:00:00',
                    'estado' => 'disponible',
                ]);
            }
        }
    }
}
