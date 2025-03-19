<?php

// App\Services\Kanban\KanbanColumnService.php

namespace App\Services\Kanban;

use App\Models\KanbanBoard;
use App\Models\KanbanColumn;
use Illuminate\Support\Facades\Gate;

class KanbanColumnService
{
    public function crearColumna(array $data, int $tableroId)
    {
        $tablero = KanbanBoard::findOrFail($tableroId);
        if (!Gate::allows('update', $tablero->proyecto)) {
            throw new \Exception('No tienes permiso para crear columnas en este tablero.');
        }

        return KanbanColumn::create([
            'kanban_board_id' => $tableroId,
            'nombre' => $data['nombre'],
        ]);
    }

    public function mostrarColumnas(int $tableroId)
    {
        $kanbanBoard = KanbanBoard::findOrFail($tableroId);
        return $kanbanBoard->columns()->with('tasks.tarea')->get();
    }

    public function eliminarColumna(int $columnaId)
    {
        $columna = KanbanColumn::findOrFail($columnaId);
        if (!Gate::allows('delete', $columna->board->proyecto)) {
            throw new \Exception('No tienes permiso para eliminar esta columna.');
        }

        // Verificar si la columna tiene tareas
        if ($columna->tasks()->count() > 0) {
            throw new \Exception('No se puede eliminar la columna porque tiene tareas. Elimine las tareas primero.');
        }

        $columna->delete();
    }
}
