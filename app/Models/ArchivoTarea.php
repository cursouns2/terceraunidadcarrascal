<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivoTarea extends Model
{
    use HasFactory;

    protected $table = 'archivo_tareas';

    protected $fillable = [
        'tarea_id',
        'nombre',
        'ruta',
        'usuario_id'
    ];

    public function tarea()
    {
        return $this->belongsTo(Tarea::class, 'tarea_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
    public function versiones()
    {
        return $this->hasMany(VersionArchivo::class, 'archivo_tarea_id');
    }
}
