<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Proyecto;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProyectoPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Proyecto $proyecto)
    {
        return $user->id === $proyecto->usuario_id || $proyecto->usuarios->contains($user);
    }

    public function update(User $user, Proyecto $proyecto)
    {
        // 1. El creador del proyecto siempre puede editarlo.
        if ($user->id === $proyecto->usuario_id) {
            return true;
        }

        // 2. Verificar si el usuario tiene el rol de 'administrador' o 'jefe' *dentro del proyecto*.
        $proyectoRole = $proyecto->usuarios()
            ->where('users.id', $user->id)
            ->value('proyecto_usuario.proyecto_role');  // Obtener el rol específico del proyecto

        if ($proyectoRole === 'administrador' || $proyectoRole === 'jefe') {
            return true;
        }

        return false;
    }

    public function delete(User $user, Proyecto $proyecto)
    {
        return $user->id === $proyecto->usuario_id;
    }

    public function assignRoles(User $user, Proyecto $proyecto)
    {
        return $user->id === $proyecto->usuario_id;
    }
}
