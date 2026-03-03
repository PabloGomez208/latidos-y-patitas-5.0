<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudAdopcion extends Model
{
    protected $table = 'solicitudes_adopcion';
    protected $primaryKey = 'id_solicitud';
    public $timestamps = false;
    
    protected $fillable = [
        'id_cliente',
        'id_mascota',
        'fecha_solicitud',
        'estado',
        'motivo',
        'direccion',
        'tiene_mascotas',
        'experiencia',
        'aprobado_por',
        'fecha_revision',
        'nota_revision'
    ];
    
    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Usuario::class, 'id_cliente', 'id_usuario');
    }
    
    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'id_mascota', 'id_mascota');
    }
}
