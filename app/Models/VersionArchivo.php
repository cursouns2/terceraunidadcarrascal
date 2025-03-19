<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VersionArchivo extends Model
{
    protected $table = 'versiones_archivo';

    protected $fillable = [
        'archivo_tarea_id', 'nombre', 'ruta', 'usuario_id',
        'comentario', 'fecha_subida', 'es_final'
    ];

    public function archivoTarea()
    {
        return $this->belongsTo(ArchivoTarea::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
