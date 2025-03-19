<?php

namespace App\Http\Controllers;

use App\Services\ArchivoTareaService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Auth;

class ArchivoController extends Controller
{
    protected $archivoTareaService;

    public function __construct(ArchivoTareaService $archivoTareaService)
    {
        $this->archivoTareaService = $archivoTareaService;
    }

    /**
     * Sube un archivo adjunto a una tarea.
     */
    public function subirArchivo(Request $request, int $tareaId)
    {
        $request->validate([
            'archivo' => 'required|file',
            'comentario' => 'nullable|string',
        ]);

        try {
            $version = $this->archivoTareaService->subirArchivo(
                $request->file('archivo'),
                $tareaId,
                Auth::id(), // Usando Auth::id()
                $request->input('comentario')
            );

            return response()->json([
                'success' => true,
                'mensaje' => 'Archivo subido correctamente',
                'version' => $version,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al subir el archivo: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene los archivos adjuntos de una tarea.
     */
    public function obtenerArchivos(int $tareaId)
    {
        try {
            $archivos = $this->archivoTareaService->obtenerArchivos($tareaId);
            return response()->json(['success' => true, 'archivos' => $archivos]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los archivos: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Descarga un archivo adjunto.
     */
    public function descargarArchivo(int $archivoId): StreamedResponse
    {
        try {
            return $this->archivoTareaService->descargarArchivo($archivoId);
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }
    }
}
