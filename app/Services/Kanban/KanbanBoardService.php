<?php

// App\Services\Kanban\KanbanBoardService.php

namespace App\Services\Kanban;

use App\Models\KanbanBoard;
use App\Models\Proyecto;
use Illuminate\Support\Facades\Gate;

class KanbanBoardService
{
    public function crearTablero(array $data, int $proyectoId)
    {
        $proyecto = Proyecto::findOrFail($proyectoId);
        if (!Gate::allows('update', $proyecto)) {
            throw new \Exception('No tienes permiso para crear tableros en este proyecto.');
        }

        return KanbanBoard::create([
            'proyecto_id' => $proyectoId,
            'nombre' => $data['nombre'],
        ]);
    }

    public function editarTablero(array $data, int $tableroId)
    {
        $tablero = KanbanBoard::findOrFail($tableroId);
        if (!Gate::allows('update', $tablero->proyecto)) {
            throw new \Exception('No tienes permiso para editar este tablero.');
        }

        $tablero->update([
            'nombre' => $data['nombre'],
        ]);

        return $tablero;
    }

    public function eliminarTablero(int $tableroId)
    {
        $tablero = KanbanBoard::findOrFail($tableroId);
        if (!Gate::allows('delete', $tablero->proyecto)) {
            throw new \Exception('No tienes permiso para eliminar este tablero.');
        }

        // Verificar si el tablero tiene columnas
        if ($tablero->columns()->count() > 0) {
            throw new \Exception('No se puede eliminar el tablero porque tiene columnas. Elimine las columnas primero.');
        }

        $tablero->delete();
    }
}
