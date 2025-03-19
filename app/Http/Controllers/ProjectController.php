<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    /**
     * Muestra todos los proyectos del usuario autenticado.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();

        $proyectos = Proyecto::where('usuario_id', $user->id)
            ->orWhere(function ($query) use ($user) {
                $query->whereHas('usuarios', function ($query) use ($user) {
                    $query->where('users.id', $user->id);
                });
            })->get();

        $usuarios = User::where('id', '!=', Auth::id())->get();

        return view('proyectos.index', compact('proyectos', 'usuarios'));
    }

    /**
     * Muestra el formulario para crear un nuevo proyecto.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $usuarios = User::where('id', '!=', Auth::id())->get();
        return view('proyectos.create', compact('usuarios'));
    }

    /**
     * Guarda un nuevo proyecto.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validar los datos del proyecto
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'usuarios' => 'required|array',
        ]);

        try {
            // Crear el proyecto
            $proyecto = Proyecto::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'usuario_id' => Auth::id(),
            ]);

            // Asignar el rol de "administrador" al creador del proyecto (ANTES del bucle)
            $proyecto->usuarios()->attach(Auth::id(), ['proyecto_role' => 'administrador']);

            // Procesar la asignación de usuarios y roles del proyecto
            foreach ($request->usuarios as $usuarioId => $data) {
                if (isset($data['id'])) {
                    $proyectoRole = $data['role'] ?? 'miembro'; // Valor por defecto si no se selecciona
                    $proyecto->usuarios()->attach($usuarioId, ['proyecto_role' => $proyectoRole]);
                }
            }

            return redirect()->route('proyectos.index')->with('success', 'Proyecto creado exitosamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al crear el proyecto: ' . $e->getMessage()]);
        }
    }

    /**
     * Muestra los detalles de un proyecto específico.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Proyecto $proyecto)
    {
        if (!Gate::allows('view-proyecto', $proyecto)) {
            abort(403, 'No tienes permiso para ver este proyecto.');
        }

        $proyecto->load('usuarios');

        return response()->json(['proyecto' => $proyecto]);
    }

    /**
     * Muestra el formulario para editar un proyecto.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Proyecto $proyecto)
    {
        if (!Gate::allows('update', $proyecto)) {
            throw new AuthorizationException('No tienes permiso para editar este proyecto.');
        }

        $usuarios = User::where('id', '!=', Auth::id())->get();
        return view('proyectos.edit', compact('proyecto', 'usuarios'));
    }

    /**
     * Actualiza un proyecto existente.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Proyecto $proyecto)
    {
        // Validar los datos del proyecto
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'usuarios' => 'required|array',
        ]);

        try {
            // Actualizar el proyecto
            $proyecto->update($request->only(['nombre', 'descripcion', 'fecha_inicio', 'fecha_fin']));

            // Sincronizar usuarios asignados al proyecto y sus roles
            $proyecto->usuarios()->detach(); // Eliminar todos los usuarios asignados anteriormente
            foreach ($request->usuarios as $usuarioId => $data) {
                if (isset($data['id'])) {
                    $proyectoRole = $data['role'];
                    $proyecto->usuarios()->attach($usuarioId, ['proyecto_role' => $proyectoRole]);
                }
            }

            // Redirigir a la lista de proyectos
            return redirect()->route('proyectos.index')->with('success', 'Proyecto actualizado exitosamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar el proyecto: ' . $e->getMessage()]);
        }
    }

    /**
     * Elimina un proyecto.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Proyecto $proyecto)
    {
        // Verificar si el usuario autenticado es el creador del proyecto
        if ($proyecto->usuario_id !== Auth::id()) {
            abort(403, 'No tienes permiso para eliminar este proyecto.');
        }

        try {
            $proyecto->delete();
            return redirect()->route('proyectos.index')->with('success', 'Proyecto eliminado exitosamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al eliminar el proyecto: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtiene los usuarios asignados a un proyecto específico.
     *
     * @param  int  $proyectoId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsuarios($proyectoId)
    {
        $proyecto = Proyecto::findOrFail($proyectoId);
        $usuarios = $proyecto->usuarios;

        return response()->json([
            'success' => true,
            'usuarios' => $usuarios,
        ]);
    }
}
