<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insertOrIgnore([
            ['nombre_rol' => 'admin'],
            ['nombre_rol' => 'veterinario'],
            ['nombre_rol' => 'cliente'],
        ]);

        DB::table('metodos_pago')->insertOrIgnore([
            ['nombre' => 'Efectivo'],
            ['nombre' => 'Tarjeta'],
            ['nombre' => 'PSE'],
            ['nombre' => 'Nequi'],
            ['nombre' => 'Daviplata'],
        ]);

        $adminRoleId = DB::table('roles')->where('nombre_rol', 'admin')->value('id_rol');
        $vetRoleId   = DB::table('roles')->where('nombre_rol', 'veterinario')->value('id_rol');

        $adminUser = DB::table('usuarios')->where('email', 'admin@example.com')->first();
        if (!$adminUser) {
            DB::table('usuarios')->insert([
                'nombre'   => 'Administrador',
                'email'    => 'admin@example.com',
                'password' => Hash::make('admin1234'),
                'telefono' => '0000000000',
                'id_rol'   => $adminRoleId,
                'created_at' => now(),
            ]);
            $adminUser = DB::table('usuarios')->where('email', 'admin@example.com')->first();
        }

        $adminId = $adminUser->id_usuario ?? null;
        if ($adminId) {
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
        }

        $vetUser = DB::table('usuarios')->where('email', 'vet@example.com')->first();
        if (!$vetUser) {
            DB::table('usuarios')->insert([
                'nombre'   => 'Veterinario',
                'email'    => 'vet@example.com',
                'password' => Hash::make('vet1234'),
                'telefono' => '0000000001',
                'id_rol'   => $vetRoleId,
                'created_at' => now(),
            ]);
            $vetUser = DB::table('usuarios')->where('email', 'vet@example.com')->first();
        }

        if ($vetUser) {
            $vetId = DB::table('veterinarios')->where('id_usuario', $vetUser->id_usuario)->value('id_veterinario');
            if (!$vetId) {
                DB::table('veterinarios')->insert([
                    'id_usuario'   => $vetUser->id_usuario,
                    'especialidad' => 'General',
                ]);
                $vetId = DB::table('veterinarios')->where('id_usuario', $vetUser->id_usuario)->value('id_veterinario');
            }

            if ($vetId) {
                $slotDates = [
                    date('Y-m-d', strtotime('+1 day')),
                    date('Y-m-d', strtotime('+2 day')),
                    date('Y-m-d', strtotime('+3 day')),
                ];
                foreach ($slotDates as $d) {
                    $exists = DB::table('disponibilidad_citas')
                        ->where('id_veterinario', $vetId)
                        ->where('fecha', $d)
                        ->where('hora_inicio', '09:00:00')
                        ->exists();
                    if (!$exists) {
                        DB::table('disponibilidad_citas')->insert([
                            'id_veterinario' => $vetId,
                            'fecha' => $d,
                            'hora_inicio' => '09:00:00',
                            'hora_fin' => '10:00:00',
                            'estado' => 'disponible',
                        ]);
                    }
                    $exists2 = DB::table('disponibilidad_citas')
                        ->where('id_veterinario', $vetId)
                        ->where('fecha', $d)
                        ->where('hora_inicio', '10:00:00')
                        ->exists();
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
    }
}
