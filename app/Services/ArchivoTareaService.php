<?php

namespace App\Services;

use App\Models\ArchivoTarea;
use App\Models\Tarea;
use App\Models\VersionArchivo;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Str;

class ArchivoTareaService
{
    public function subirArchivo($archivo, int $tareaId, int $usuarioId, ?string $comentario = null): VersionArchivo
    {
        $tarea = Tarea::findOrFail($tareaId);
        $fileSizeLimit = $tarea->file_size_limit ?? config('app.default_file_size_limit');

        if ($archivo->getSize() > $fileSizeLimit) {
            throw new \Exception("El tamaño del archivo excede el límite permitido.");
        }

        $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
        $ruta = $archivo->storeAs('archivos_tareas', $nombreArchivo, 'public');

        $archivoTarea = ArchivoTarea::create([
            'tarea_id' => $tareaId,
            'nombre' => $archivo->getClientOriginalName(),
            'ruta' => $ruta,
            'usuario_id' => $usuarioId,
        ]);

        return VersionArchivo::create([
            'archivo_tarea_id' => $archivoTarea->id,
            'nombre' => $archivo->getClientOriginalName(),
            'ruta' => $ruta,
            'usuario_id' => $usuarioId,
            'comentario' => $comentario,
            'fecha_subida' => now(),
        ]);
    }

    public function subirArchivoEnlace(string $enlace, string $nombreArchivo, int $tareaId, int $usuarioId, ?string $comentario = null): VersionArchivo
    {
        $archivoTarea = ArchivoTarea::create([
            'tarea_id' => $tareaId,
            'nombre' => $nombreArchivo,
            'ruta' => $enlace, // Guarda el enlace en lugar de la ruta local
            'usuario_id' => $usuarioId,
        ]);

        return VersionArchivo::create([
            'archivo_tarea_id' => $archivoTarea->id,
            'nombre' => $nombreArchivo,
            'ruta' => $enlace,
            'usuario_id' => $usuarioId,
            'comentario' => $comentario,
            'fecha_subida' => now(),
        ]);
    }

    public function obtenerArchivos(int $tareaId)
    {
        $tarea = Tarea::findOrFail($tareaId);
        return $tarea->archivos()->with('versiones')->get();
    }

    public function descargarArchivo(int $archivoId): BinaryFileResponse
    {
        $archivoTarea = ArchivoTarea::findOrFail($archivoId);
        $filePath = $archivoTarea->ruta;

        // Verifica si la ruta es un enlace de Google Drive
        if (Str::startsWith($filePath, 'https://')) {
            throw new \Exception("Este archivo está almacenado en Google Drive. No se puede descargar directamente.");
        }

        if (!Storage::disk('public')->exists($filePath)) {
            throw new \Exception("Archivo no encontrado.");
        }

        $pathToFile = storage_path("app/public/" . $filePath);
        $response = response()->download($pathToFile, $archivoTarea->nombre);

        return $response;
    }
}
