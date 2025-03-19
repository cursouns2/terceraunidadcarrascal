<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KanbanBoard extends Model
{
    use HasFactory;

    protected $table = 'kanban_boards';  // Nombre de la tabla

    protected $fillable = [
        'proyecto_id',
        'nombre',
    ];

    // Relación con KanbanColumns (un tablero puede tener muchas columnas)
    public function columns()
    {
        return $this->hasMany(KanbanColumn::class, 'kanban_board_id');
    }

    // Relación con Proyectos
    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }
}
