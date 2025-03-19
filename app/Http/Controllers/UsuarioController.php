<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    /**
     * Muestra la lista de todos los usuarios.
     */
    public function index()
    {
        // Obtiene todos los usuarios
        $usuarios = User::all();

        // Retorna la vista con los usuarios
        return view('index-usuarios', compact('usuarios'));
    }



    /**
     * Muestra los datos de un usuario para ser editado.
     */
    public function edit($id)
    {
        // Buscar el usuario por ID
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json([
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        // Retorna los datos del usuario en formato JSON
        return response()->json([
            'message' => 'Datos del usuario obtenidos exitosamente.',
            'usuario' => $usuario,
        ], 200);
    }

    /**
     * Actualiza el rol de un usuario.
     */
    public function update(Request $request, $id)
    {
        // Validar que el rol sea válido
        $request->validate([
            'role' => 'required|in:administrador,jefe,miembro',
        ]);

        // Buscar el usuario por ID
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json([
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        // Actualizar el rol
        $usuario->role = $request->input('role');
        $usuario->save();

        // Retorna la confirmación de la actualización
        return response()->json([
            'message' => 'Rol actualizado exitosamente.',
            'usuario' => $usuario,
        ], 200);
    }

    /**
     * Elimina un usuario.
     */
    public function destroy($id)
    {
        // Buscar el usuario por ID
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json([
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        // Eliminar el usuario
        $usuario->delete();

        // Retorna la confirmación de eliminación
        return response()->json([
            'message' => 'Usuario eliminado exitosamente.',
        ], 200);
    }
}
