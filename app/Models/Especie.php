<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especie extends Model
{
    protected $table = 'especies';
    protected $primaryKey = 'id_especie';
    public $timestamps = false;
    
    protected $fillable = [
        'nombre',
        'activo'
    ];
}
