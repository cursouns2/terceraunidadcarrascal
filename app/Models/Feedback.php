<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = ['tarea_id', 'usuario_id', 'comentario', 'archivo_adjunto'];

    public function tarea()
    {
        return $this->belongsTo(Tarea::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
