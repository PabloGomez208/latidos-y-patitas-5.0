<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactoMensaje extends Model
{
    protected $table = 'contacto_mensajes';
    protected $primaryKey = 'id_mensaje';
    public $timestamps = false;
    
    protected $fillable = [
        'nombre',
        'email',
        'asunto',
        'mensaje',
        'fecha_envio',
        'estado'
    ];
}
