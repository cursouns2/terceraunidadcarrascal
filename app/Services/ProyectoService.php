<?php

namespace App\Services;

use App\Models\Proyecto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProyectoService
{
    public function obtenerProyectosParaUsuario()
    {
        $user = Auth::user();

        $proyectos = Proyecto::where('usuario_id', $user->id)
            ->orWhere(function ($query) use ($user) {
                $query->whereHas('usuarios', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            })
            ->get();

        // Usamos un array asociativo para eliminar duplicados y mantener los roles de proyecto
        $proyectosArray = [];
        foreach ($proyectos as $proyecto) {
            $proyectosArray[$proyecto->id] = $proyecto;
        }

        // Ordenar por nombre
        usort($proyectosArray, function ($a, $b) {
            return strcmp($a->nombre, $b->nombre);
        });

        return collect($proyectosArray);
    }

    public function obtenerTableros(int $proyectoId)
    {
        $proyecto = Proyecto::findOrFail($proyectoId);

        $tableros = $proyecto->kanbanBoards()->get();

        return $tableros;
    }
}
