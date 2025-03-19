<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\Tarea;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View; // Importa la clase View
use Illuminate\Support\Facades\DB;  // Importa la clase DB
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Obtener proyectos y tareas del usuario autenticado
        $proyectos = $user->proyectos;
        $tareas = Tarea::whereHas('usuarios', function($query) use ($user) {
            $query->where('usuario_id', $user->id);
        })->get();

        // Conteos generales
        $totalProyectos = $proyectos->count();
        $totalTareas = $tareas->count();
        $tareasCompletadas = $tareas->where('estado', 'completada')->count();
        $tareasPendientes = $tareas->where('estado', '!=', 'completada')->count();

        // Proyectos próximos a vencer (en los próximos 7 días)
        $proyectosProximos = $proyectos->filter(function ($proyecto) {
            return Carbon::parse($proyecto->fecha_fin)->gt(Carbon::now()) && Carbon::parse($proyecto->fecha_fin)->lte(Carbon::now()->addDays(7));
        });

        // Proyectos atrasados (fecha_fin anterior a hoy)
        $proyectosAtrasados = $proyectos->filter(function ($proyecto) {
            return Carbon::parse($proyecto->fecha_fin)->lt(Carbon::now());
        });


        // Pasar todas las variables a la vista
        return View::make('dashboard', compact(
            'totalProyectos',
            'totalTareas',
            'tareasCompletadas',
            'tareasPendientes',
            'proyectos',
            'proyectosProximos',
            'proyectosAtrasados' // Agregamos esta variable
        ));
    }
}
