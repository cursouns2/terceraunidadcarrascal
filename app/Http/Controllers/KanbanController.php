<?php

namespace App\Http\Controllers;

use App\Services\Kanban\KanbanBoardService;
use App\Services\Kanban\KanbanColumnService;
use App\Services\Kanban\KanbanTareaService;
use App\Services\ProyectoService;
use App\Services\ArchivoTareaService;
use App\Services\FeedbackService;
use App\Models\User;
use App\Models\Tarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use Illuminate\Support\Str;

class KanbanController extends Controller
{
    protected $kanbanBoardService;
    protected $kanbanColumnService;
    protected $kanbanTareaService;
    protected $proyectoService;
    protected $archivoTareaService;
    protected $feedbackService;

    public function __construct(
        KanbanBoardService $kanbanBoardService,
        KanbanColumnService $kanbanColumnService,
        KanbanTareaService $kanbanTareaService,
        ProyectoService $proyectoService,
        ArchivoTareaService $archivoTareaService,
        FeedbackService $feedbackService
    ) {
        $this->kanbanBoardService = $kanbanBoardService;
        $this->kanbanColumnService = $kanbanColumnService;
        $this->kanbanTareaService = $kanbanTareaService;
        $this->proyectoService = $proyectoService;
        $this->archivoTareaService = $archivoTareaService;
        $this->feedbackService = $feedbackService;
    }

    /**
     * Muestra el tablero Kanban.
     */
    public function index()
    {
        $proyectos = $this->proyectoService->obtenerProyectosParaUsuario();
        $usuarios = User::all();
        return view('kanban.kanban', compact('proyectos', 'usuarios'));
    }

    /**
     * Crea un nuevo tablero para un proyecto específico.
     */
    public function crearTablero(Request $request, $proyectoId)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        try {
            $tablero = $this->kanbanBoardService->crearTablero($request->all(), $proyectoId);
            return response()->json(['success' => true, 'tablero' => $tablero], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtiene todos los tableros de un proyecto.
     */
    public function obtenerTableros(int $proyectoId)
    {
        try {
            $tableros = $this->proyectoService->obtenerTableros($proyectoId);

            $tablerosSimples = $tableros->map(function ($tablero) {
                return [
                    'id' => $tablero->id,
                    'nombre' => $tablero->nombre,
                    'columns' => $tablero->columns->map(function ($column) {
                        return [
                            'id' => $column->id,
                            'nombre' => $column->nombre,
                            'tasks' => $column->tasks->map(function ($task) {
                                return [
                                    'id' => $task->id,
                                    'tarea_id' => $task->tarea_id,
                                    'nombre' => $task->tarea->nombre,
                                    'descripcion' => $task->tarea->descripcion,
                                    'estado' => $task->tarea->estado,
                                    'prioridad' => $task->tarea->prioridad,
                                    'fecha_vencimiento' => $task->tarea->fecha_vencimiento,
                                    'usuarios' => $task->tarea->usuarios,
                                ];
                            }),
                        ];
                    }),
                ];
            });

            return response()->json(['success' => true, 'tableros' => $tablerosSimples]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Edita un tablero existente.
     */
    public function editarTablero(Request $request, $proyectoId, $tableroId)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        try {
            $tablero = $this->kanbanBoardService->editarTablero($request->all(), $tableroId);
            return response()->json(['success' => true, 'tablero' => $tablero]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Elimina un tablero.
     */
    public function eliminarTablero($proyectoId, $tableroId)
    {
        try {
            $this->kanbanBoardService->eliminarTablero($tableroId);
            return response()->json(['success' => true, 'message' => 'Tablero eliminado exitosamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Crea una nueva columna en un tablero.
     */
    public function crearColumna(Request $request, $tableroId)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        try {
            $columna = $this->kanbanColumnService->crearColumna($request->all(), $tableroId);
            return response()->json(['success' => true, 'columna' => $columna], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Muestra todas las columnas de un tablero.
     */
    public function mostrarColumnas(int $tableroId)
    {
        try {
            $columnas = $this->kanbanColumnService->mostrarColumnas($tableroId);
            return response()->json(['success' => true, 'columnas' => $columnas]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Elimina una columna específica.
     */
    public function eliminarColumna(int $columnaId)
    {
        try {
            $this->kanbanColumnService->eliminarColumna($columnaId);
            return response()->json(['success' => true, 'message' => 'Columna eliminada exitosamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Crea una nueva tarea en una columna.
     */
    public function crearTarea(Request $request, int $columnaId)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:pendiente,en progreso,completada',
            'prioridad' => 'required|in:baja,media,alta',
            'fecha_vencimiento' => 'required|date',
            'usuario_id' => 'required|exists:users,id',
            'file_size_limit' => 'nullable|integer|min:1|max:100000',
            'archivos.*' => 'nullable|file|max:10240',
        ]);

        try {
            $tarea = $this->kanbanTareaService->crearTarea($request->all(), $columnaId);
            return response()->json(['success' => true, 'mensaje' => 'Tarea creada correctamente', 'tarea' => $tarea]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al crear la tarea: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtiene los detalles de una tarea específica.
     */
    public function obtenerTarea(int $tareaId)
    {
        try {
            $tarea = Tarea::findOrFail($tareaId);
            return response()->json(['success' => true, 'tarea' => $tarea]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Edita una tarea existente.
     */
    public function editarTarea(Request $request, int $tareaId)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:pendiente,en progreso,completada',
            'prioridad' => 'required|in:baja,media,alta',
            'fecha_vencimiento' => 'required|date',
            'usuario_id' => 'required|exists:users,id',
            'file_size_limit' => 'nullable|integer|min:1|max:100000',
        ]);

        try {
            $tarea = $this->kanbanTareaService->editarTarea($request->all(), $tareaId);
            return response()->json(['success' => true, 'mensaje' => 'Tarea actualizada correctamente', 'tarea' => $tarea]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar la tarea: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Elimina una tarea.
     */
    public function eliminarTarea(int $tareaId)
    {
        try {
            $this->kanbanTareaService->eliminarTarea($tareaId);
            return response()->json(['success' => true, 'message' => 'Tarea eliminada exitosamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Subir un archivo a una tarea.
     */
    public function subirArchivo(Request $request, int $tareaId)
    {
        $request->validate([
            'archivo' => 'required|file|max:10240',
        ]);

        $archivo = $request->file('archivo');
        $extension = $archivo->getClientOriginalExtension();
        $esArchivoOffice = in_array($extension, ['docx', 'xlsx', 'pptx', 'doc', 'xls', 'ppt']);

        try {
            if ($esArchivoOffice) {
                // Configurar la conexión con Google Drive
                $client = new Client();
                $client->setAuthConfig(storage_path('app/google-drive/producto-454004-517d93f61ca0.json'));
                $client->setScopes([Drive::DRIVE]);

                // Deshabilitar la verificación SSL (¡Solo para pruebas!)
                $client->setHttpClient(new \GuzzleHttp\Client([
                    'verify' => false,
                ]));

                $service = new Drive($client);

                // Subir el archivo a Google Drive
                $fileMetadata = new DriveFile([
                    'name' => $archivo->getClientOriginalName()
                ]);
                $content = file_get_contents($archivo->getRealPath());
                $file = $service->files->create($fileMetadata, [
                    'data' => $content,
                    'mimeType' => $archivo->getMimeType(),
                    'uploadType' => 'multipart',
                    'fields' => 'id,webViewLink,webContentLink'
                ]);

                // Obtener el enlace compartido
                $permiso = new Permission([
                    'type' => 'anyone',
                    'role' => 'writer', //Permite que los archivos se puedan editar
                ]);
                $service->permissions->create($file->id, $permiso);
                $enlaceCompartido = $file->webViewLink; // Guardar el enlace de Google Drive

                // Usar el nuevo método subirArchivoEnlace
                $version = $this->archivoTareaService->subirArchivoEnlace(
                    $enlaceCompartido,
                    $archivo->getClientOriginalName(),
                    $tareaId,
                    Auth::id(),
                    $request->input('comentario')
                );
                return response()->json(['success' => true, 'mensaje' => 'Archivo subido correctamente a Google Drive', 'version' => $version]);
            } else {
                // Subir el archivo al servidor (lógica existente)
                $version = $this->archivoTareaService->subirArchivo(
                    $archivo,
                    $tareaId,
                    Auth::id(),
                    $request->input('comentario')
                );
                return response()->json(['success' => true, 'mensaje' => 'Archivo subido correctamente al servidor', 'version' => $version]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar el límite de tamaño de archivo para una tarea.
     */
    public function actualizarLimiteArchivo(Request $request, int $tareaId)
    {
        try {
            $tarea = $this->kanbanTareaService->actualizarLimiteArchivo($request->all(), $tareaId);
            return response()->json(['success' => true, 'message' => 'Límite de archivo actualizado exitosamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Agregar feedback a una tarea.
     */
    public function agregarFeedback(Request $request, int $tareaId)
    {
        try {
            $feedback = $this->feedbackService->agregarFeedback(
                $tareaId,
                Auth::id(),
                $request->input('comentario'),
                $request->file('archivo')
            );

            return response()->json([
                'success' => true,
                'mensaje' => 'Feedback agregado correctamente',
                'feedback' => $feedback,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener feedback de una tarea.
     */
    public function obtenerFeedback(int $tareaId)
    {
        try {
            $feedback = $this->feedbackService->obtenerFeedback($tareaId);
            return response()->json(['success' => true, 'feedback' => $feedback, 'base_url' => asset('/')]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener archivos de una tarea.
     */
    public function obtenerArchivos(int $tareaId)
    {
        try {
            $archivos = $this->archivoTareaService->obtenerArchivos($tareaId);
            return response()->json(['success' => true, 'archivos' => $archivos]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Descargar un archivo.
     */
    public function descargarArchivo(int $archivoId)
    {
        try {
            $archivo = $this->archivoTareaService->descargarArchivo($archivoId);
            // Since descargarArchivo returns a StreamedResponse, we just return it directly
            return $archivo;
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Mover una tarea a otra columna.
     */
    public function moveTask(Request $request, int $tareaId)
    {
        try {
            $this->kanbanTareaService->moveTask($request->all(), $tareaId);
            return response()->json(['success' => true, 'message' => 'Tarea movida exitosamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
