<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KanbanColumn extends Model
{
    use HasFactory;

    protected $table = 'kanban_columns';  // Nombre de la tabla

    protected $fillable = [
        'kanban_board_id',
        'nombre',
        'orden'
    ];

    // Relación con KanbanTasks (una columna puede tener muchas tareas)
    public function tasks()
    {
        return $this->hasMany(KanbanTask::class, 'kanban_column_id');
    }

    // Relación con KanbanBoard
    public function board()
    {
        return $this->belongsTo(KanbanBoard::class, 'kanban_board_id');
    }
}
