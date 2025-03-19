<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarea extends Model
{
    use HasFactory;

    protected $table = 'tareas';

    protected $fillable = [
        'proyecto_id',
        'nombre',
        'descripcion',
        'estado',
        'prioridad',
        'fecha_vencimiento',
        'file_size_limit'
    ];

    // En Tarea
    public function columna()
    {
        return $this->belongsTo(KanbanColumn::class, 'kanban_column_id');
    }


    // Relación: Una tarea pertenece a un proyecto
    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    // Relación: Una tarea puede tener múltiples usuarios asignados (a través de TareaUsuario)
    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'tarea_usuario', 'tarea_id', 'usuario_id')
            ->withPivot('asignado_en');
    }

    public function kanbanTasks()
    {
        return $this->hasMany(KanbanTask::class);
    }

    public function archivos()
    {
        return $this->hasMany(ArchivoTarea::class);
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class, 'tarea_id');
    }
}
