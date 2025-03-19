<?php

namespace App\Http\Controllers;

use App\Models\Tarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TareaController extends Controller
{
    /**
     * Muestra los detalles de una tarea específica.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $tarea = Tarea::with(['usuarios', 'proyecto', 'archivos.versiones', 'feedback.usuario'])->findOrFail($id);

            // Validar permisos del usuario
            if (!Gate::allows('view', $tarea->proyecto)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para ver esta tarea.'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'tarea' => $tarea,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la tarea: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualiza una tarea existente.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validar los datos de la tarea
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:pendiente,en progreso,completada',
            'prioridad' => 'required|in:baja,media,alta',
            'fecha_vencimiento' => 'required|date',
            'usuario_id' => 'required|exists:users,id',
        ]);

        $tarea = Tarea::findOrFail($id);

        // Validar permisos del usuario
        if (!Gate::allows('update', $tarea->proyecto)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para actualizar esta tarea.'
            ], 403);
        }

        try {
            // Actualizar la tarea
            $tarea->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'estado' => $request->estado,
                'prioridad' => $request->prioridad,
                'fecha_vencimiento' => $request->fecha_vencimiento,
            ]);

            // Actualizar asignación de usuarios
            $tarea->usuarios()->sync($request->usuario_id);

            return response()->json([
                'success' => true,
                'mensaje' => 'Tarea actualizada correctamente',
                'tarea' => $tarea->load('usuarios'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al actualizar la tarea: ' . $e->getMessage(),
            ], 500);
        }
    }
}
