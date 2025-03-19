<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProjectRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
         // Verificar si el usuario está autenticado
        if (!$request->user()) {
            abort(403, 'Acceso no autorizado.');
        }

        // Obtiene el proyecto actual (si es aplicable a esta ruta)
        $proyecto = $request->route('proyecto'); // Ajusta esto si tu ruta usa un nombre diferente para el parámetro del proyecto

        // Si no hay proyecto o el usuario no está asociado al proyecto, deniega el acceso
        if (!$proyecto || !$proyecto->usuarios()->where('users.id', $request->user()->id)->exists()) {
            abort(403, 'Acceso no autorizado.');
        }

        // Obtiene el rol del usuario en el proyecto
        $proyectoRole = $proyecto->usuarios()->where('users.id', $request->user()->id)->first()->pivot->proyecto_role;

        // Verifica si el rol del usuario en el proyecto está en la lista de roles permitidos
        if (!in_array($proyectoRole, $roles)) {
            abort(403, 'Acceso no autorizado.');
        }

        return $next($request);
    }
}
