<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TareaUsuario extends Model
{
    use HasFactory;

    protected $table = 'tarea_usuario'; // Nombre de la tabla pivot

    protected $fillable = [
        'tarea_id',
        'usuario_id',
        'asignado_en'
    ];

    public $timestamps = true; // Habilita el manejo automático de created_at y updated_at

    // Relación con Tarea
    public function tarea()
    {
        return $this->belongsTo(Tarea::class, 'tarea_id');
    }

    // Relación con Usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
