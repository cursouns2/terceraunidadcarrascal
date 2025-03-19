<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class JitsiController extends Controller
{
    public function createRoom(Request $request)
    {
        $request->validate([
            'room_name' => 'required|string|max:255',
        ]);

        // Obtener el nombre de la sala
        $roomName = $request->input('room_name');

        // Generar el enlace a la sala de Jitsi Meet
        $jitsiUrl = "https://meet.jit.si/" . urlencode($roomName);

        // Redirigir al usuario a la sala creada
        return redirect()->to($jitsiUrl);
    }
}

