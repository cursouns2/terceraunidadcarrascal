<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    use HasFactory;

    protected $table = 'proyectos';

    protected $fillable = ['nombre', 'descripcion', 'fecha_inicio', 'fecha_fin', 'usuario_id'];

    // Un proyecto tiene muchas tareas
    public function tareas()
    {
        return $this->hasMany(Tarea::class, 'proyecto_id');
    }

    // Un proyecto puede tener muchos usuarios a través de una tabla pivote
    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'proyecto_usuario', 'proyecto_id', 'usuario_id')
            ->withPivot('proyecto_role')
            ->withTimestamps();
    }

    public function jefe()
    {
        return $this->belongsTo(User::class, 'usuario_id');  // El jefe de proyecto
    }

    public function kanbanBoards()
    {
        return $this->hasMany(KanbanBoard::class);
    }
}
