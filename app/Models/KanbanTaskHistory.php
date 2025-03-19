<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KanbanTaskHistory extends Model
{
    use HasFactory;

    protected $table = 'kanban_task_history';  // Nombre de la tabla

    protected $fillable = [
        'kanban_task_id', 'from_column_id', 'to_column_id', 'moved_at'
    ];

    // Relación con KanbanTask
    public function task()
    {
        return $this->belongsTo(KanbanTask::class,'kanban_task_id');
    }

    // Relación con la columna origen
    public function fromColumn()
    {
        return $this->belongsTo(KanbanColumn::class, 'from_column_id');
    }

    // Relación con la columna destino
    public function toColumn()
    {
        return $this->belongsTo(KanbanColumn::class, 'to_column_id');
    }
}

?>