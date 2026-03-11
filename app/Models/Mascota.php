<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mascota extends Model
{
    protected $table = 'mascotas';
    protected $primaryKey = 'id_mascota';
    public $timestamps = false;
    
    protected $fillable = [
        'nombre',
        'especie',
        'raza',
        'edad',
        'sexo',
        'id_especie',
        'id_sexo',
        'descripcion',
        'imagen',
        'estado',
        'fecha_publicacion',
        'id_admin'
    ];
    
    // Relaciones
    public function administrador()
    {
        return $this->belongsTo(Usuario::class, 'id_admin', 'id_usuario');
    }
    
    public function especieRef()
    {
        return $this->belongsTo(Especie::class, 'id_especie', 'id_especie');
    }
    
    public function sexoRef()
    {
        return $this->belongsTo(Sexo::class, 'id_sexo', 'id_sexo');
    }
    
    public function historiaClinica()
    {
        return $this->hasOne(HistoriaClinica::class, 'id_mascota', 'id_mascota');
    }
    
    public function solicitudesAdopcion()
    {
        return $this->hasMany(SolicitudAdopcion::class, 'id_mascota', 'id_mascota');
    }
}
