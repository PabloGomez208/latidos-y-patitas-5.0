<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_adopcion', function (Blueprint $table) {
            $table->text('motivo')->nullable()->after('id_mascota');
            $table->string('direccion', 255)->nullable()->after('motivo');
            $table->boolean('tiene_mascotas')->default(false)->after('direccion');
            $table->text('experiencia')->nullable()->after('tiene_mascotas');
            $table->unsignedBigInteger('aprobado_por')->nullable()->after('estado');
            $table->date('fecha_revision')->nullable()->after('aprobado_por');
            $table->text('nota_revision')->nullable()->after('fecha_revision');

            $table->foreign('aprobado_por')->references('id_usuario')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_adopcion', function (Blueprint $table) {
            $table->dropForeign(['aprobado_por']);
            $table->dropColumn([
                'motivo',
                'direccion',
                'tiene_mascotas',
                'experiencia',
                'aprobado_por',
                'fecha_revision',
                'nota_revision',
            ]);
        });
    }
};
