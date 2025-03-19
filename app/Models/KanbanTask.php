<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KanbanTask extends Model
{
    use HasFactory;

    protected $table = 'kanban_tasks';  // Nombre de la tabla

    protected $fillable = [
        'kanban_column_id', 'tarea_id'
    ];

    // Relación con KanbanColumn (una tarea pertenece a una columna)
    public function column()
    {
        return $this->belongsTo(KanbanColumn::class, 'kanban_column_id');
    }

    // Relación con Tarea (una tarea puede tener información adicional)
    public function tarea()
    {
        return $this->belongsTo(Tarea::class,'tarea_id');
    }

    // Relación con el historial de tareas (cuando se mueve de columna)
    public function history()
    {
        return $this->hasMany(KanbanTaskHistory::class,'kanban_task_id');
    }
}

?>