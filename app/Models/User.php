<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'role', // Rol del sistema (administrador, jefe, miembro)
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relación con proyectos
    public function proyectos()
    {
        return $this->belongsToMany(Proyecto::class, 'proyecto_usuario', 'usuario_id', 'proyecto_id')
                     ->withPivot('proyecto_role') // Incluye el rol del proyecto
                     ->withTimestamps();
    }

    // Relación con tareas
    public function tareas()
    {
        return $this->belongsToMany(Tarea::class, 'tarea_usuario', 'usuario_id', 'tarea_id')
            ->withPivot('asignado_en');
    }

    // Métodos para verificar roles del sistema
    public function isAdmin(): bool
    {
        return $this->role === 'administrador';
    }

    public function isProjectManager(): bool
    {
        return $this->role === 'jefe';
    }

    public function isMember(): bool
    {
        return $this->role === 'miembro';
    }
}
