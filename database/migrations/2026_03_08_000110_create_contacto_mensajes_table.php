<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacto_mensajes', function (Blueprint $table) {
            $table->id('id_mensaje');
            $table->string('nombre', 100);
            $table->string('email', 150);
            $table->string('asunto', 150)->nullable();
            $table->text('mensaje');
            $table->timestamp('fecha_envio')->useCurrent();
            $table->enum('estado', ['nuevo','leido','archivado'])->default('nuevo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacto_mensajes');
    }
};
