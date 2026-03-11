<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mascotas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_especie')->nullable()->after('sexo');
            $table->unsignedBigInteger('id_sexo')->nullable()->after('id_especie');
        });
        
        if (Schema::hasTable('especies')) {
            $especies = DB::table('mascotas')->select('id_mascota','especie')->whereNotNull('especie')->get();
            foreach ($especies as $row) {
                $name = trim((string) $row->especie);
                if ($name === '') continue;
                $existing = DB::table('especies')->whereRaw('LOWER(nombre)=?', [strtolower($name)])->first();
                if (!$existing) {
                    $id = DB::table('especies')->insertGetId(['nombre' => $name, 'activo' => 1]);
                    $existing = (object) ['id_especie' => $id];
                }
                DB::table('mascotas')->where('id_mascota', $row->id_mascota)->update(['id_especie' => $existing->id_especie]);
            }
        }
        
        if (Schema::hasTable('sexos')) {
            $sexos = DB::table('mascotas')->select('id_mascota','sexo')->whereNotNull('sexo')->get();
            foreach ($sexos as $row) {
                $name = trim(strtolower((string) $row->sexo));
                if ($name === '') continue;
                if (in_array($name, ['macho','masculino'])) $name = 'Masculino';
                elseif (in_array($name, ['hembra','femenino'])) $name = 'Femenino';
                else $name = ucfirst($name);
                $existing = DB::table('sexos')->whereRaw('LOWER(nombre)=?', [strtolower($name)])->first();
                if (!$existing) {
                    $id = DB::table('sexos')->insertGetId(['nombre' => $name, 'activo' => 1]);
                    $existing = (object) ['id_sexo' => $id];
                }
                DB::table('mascotas')->where('id_mascota', $row->id_mascota)->update(['id_sexo' => $existing->id_sexo, 'sexo' => $name]);
            }
        }
        
        Schema::table('mascotas', function (Blueprint $table) {
            $table->foreign('id_especie')->references('id_especie')->on('especies')->nullOnDelete();
            $table->foreign('id_sexo')->references('id_sexo')->on('sexos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mascotas', function (Blueprint $table) {
            if (Schema::hasColumn('mascotas', 'id_especie')) {
                $table->dropForeign(['id_especie']);
                $table->dropColumn('id_especie');
            }
            if (Schema::hasColumn('mascotas', 'id_sexo')) {
                $table->dropForeign(['id_sexo']);
                $table->dropColumn('id_sexo');
            }
        });
    }
};
