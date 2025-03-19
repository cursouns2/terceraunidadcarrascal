<?php

namespace App\Services\Kanban;

use App\Models\Tarea;
use App\Models\KanbanTask;
use App\Models\KanbanColumn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class KanbanTareaService
{
    public function crearTarea(array $data, int $columnaId)
    {
        $columna = KanbanColumn::findOrFail($columnaId);
        if (!Gate::allows('update', $columna->board->proyecto)) {
            throw new \Exception('No tienes permiso para crear tareas en este tablero.');
        }

        DB::beginTransaction();
        try {
            $tarea = new Tarea([
                'proyecto_id' => $columna->board->proyecto_id,
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'],
                'estado' => $data['estado'],
                'prioridad' => $data['prioridad'],
                'fecha_vencimiento' => $data['fecha_vencimiento'],
                'file_size_limit' => $data['file_size_limit'] ? $data['file_size_limit'] * 1024 : null,
            ]);
            $tarea->save();

            $kanbanTask = new KanbanTask([
                'tarea_id' => $tarea->id,
                'kanban_column_id' => $columnaId,
            ]);
            $kanbanTask->save();

            $tarea->usuarios()->attach($data['usuario_id'], ['asignado_en' => now()]);

            DB::commit();

            return $kanbanTask->load('tarea.usuarios');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function editarTarea(array $data, int $tareaId)
    {
        $tarea = Tarea::findOrFail($tareaId);

        if (!Gate::allows('update', $tarea->proyecto)) {
            throw new \Exception('No tienes permiso para editar esta tarea.');
        }

        DB::beginTransaction();
        try {
            $tarea->update([
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'],
                'estado' => $data['estado'],
                'prioridad' => $data['prioridad'],
                'fecha_vencimiento' => $data['fecha_vencimiento'],
                'file_size_limit' => $data['file_size_limit'] ? $data['file_size_limit'] * 1024 : null,
            ]);

            $tarea->usuarios()->sync([$data['usuario_id']]);
            DB::commit();

            return $tarea->load('usuarios');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function eliminarTarea(int $tareaId)
    {
        $kanbanTask = KanbanTask::findOrFail($tareaId);
        if (!Gate::allows('delete', $kanbanTask->tarea->proyecto)) {
            throw new \Exception('No tienes permiso para eliminar esta tarea.');
        }

        $kanbanTask->delete();
        $kanbanTask->tarea->delete();
    }

    public function actualizarLimiteArchivo(array $data, int $tareaId)
    {
        $tarea = Tarea::findOrFail($tareaId);
        $tarea->update([
            'file_size_limit' => $data['file_size_limit'] * 1024, // Convertir a KB
        ]);

        return $tarea;
    }

    public function moveTask(array $data, int $tareaId)
    {
        $kanbanTask = KanbanTask::findOrFail($tareaId);

        $nuevaColumnaId = $data['kanban_column_id'];
        $nuevaColumna = KanbanColumn::findOrFail($nuevaColumnaId);

        if (!Gate::allows('update', $nuevaColumna->board->proyecto)) {
            throw new \Exception('No tienes permiso para mover tareas en este tablero.');
        }

        $kanbanTask->update([
            'kanban_column_id' => $nuevaColumnaId,
        ]);

        return $kanbanTask;
    }
}
