<?php

namespace App\Http\Controllers;

use App\Models\Tarea;
use App\Models\User;
use Illuminate\Http\Request;

class TareaUsuarioController extends Controller
{
    // Mostrar todas las tareas y los usuarios asignados a cada tarea
    public function index()
    {
        $tareas = Tarea::with('usuarios')->get(); // Obtener tareas con usuarios asignados
        return view('tarea_usuario.index', compact('tareas'));
    }

    // Mostrar formulario para asignar usuarios a una tarea
    public function create($tareaId)
    {
        $tarea = Tarea::findOrFail($tareaId);
        $usuarios = User::all(); // Obtener todos los usuarios
        return view('tarea_usuario.create', compact('tarea', 'usuarios'));
    }

    // Almacenar la asignación de un usuario a una tarea
    // Almacenar asignación de usuario
    public function store(Request $request, $tareaId)
    {
        $request->validate([
            'usuario_id' => 'required|integer|exists:usuarios,id',
        ]);

        $tarea = Tarea::findOrFail($tareaId);
        $tarea->usuarios()->attach($request->usuario_id); // Asignar usuario a la tarea

        return redirect()->route('tareas.show', $tareaId)->with('success', 'Usuario asignado a la tarea exitosamente.');
    }


    // Mostrar formulario para eliminar la asignación de un usuario de una tarea
    public function edit($tareaId)
    {
        $tarea = Tarea::findOrFail($tareaId);
        $usuarios = User::all();
        return view('tarea_usuario.edit', compact('tarea', 'usuarios'));
    }

    // Eliminar la asignación de un usuario de una tarea
    // Eliminar la asignación de un usuario de una tarea
    public function destroy(Request $request, $tareaId)
    {
        $request->validate([
            'usuario_id' => 'required|integer|exists:usuarios,id',
        ]);

        $tarea = Tarea::findOrFail($tareaId);
        $usuarioId = $request->usuario_id;

        // Verificar si el usuario ya está asignado
        if ($tarea->usuarios()->where('usuario_id', $usuarioId)->exists()) {
            $tarea->usuarios()->detach($usuarioId); // Desasignar usuario de la tarea
            return redirect()->route('tarea_usuario.index')->with('success', 'Usuario desasignado de la tarea exitosamente.');
        }

        return redirect()->route('tarea_usuario.index')->with('error', 'El usuario no estaba asignado a esta tarea.');
    }
}
