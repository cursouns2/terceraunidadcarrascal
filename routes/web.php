<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JitsiController;
use App\Http\Controllers\TareaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;

Route::post('/chatbot/response', [ChatbotController::class, 'getResponse'])->name('chatbot.response');

//Route::post('/chatbot/response', [ChatbotController::class, 'getResponse']);
Route::get('/proyectos/{proyectoId}/usuarios', [ProjectController::class, 'getUsuarios'])->name('proyectos.usuarios');
// Rutas públicas
Route::get('/', [HomeController::class, 'index'])->name('index');
Route::get('/welcome', function () {
    return view('welcome');
})->name('welcome');
Route::get('/tareas/{id}', [TareaController::class, 'show'])->name('tareas.show');
// Rutas que requieren autenticación
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tareas
    Route::get('/tareas', [TareaController::class, 'index'])->name('tareas.index');

    // Proyectos (Recurso completo)
    Route::resource('proyectos', ProjectController::class);

    // Rutas de Kanban
    Route::get('/kanban', [KanbanController::class, 'index'])->name('kanban.index');
    Route::post('/kanban/{proyectoId}/tableros', [KanbanController::class, 'crearTablero'])->name('kanban.crearTablero');
    Route::get('/kanban/{proyectoId}/tableros', [KanbanController::class, 'obtenerTableros'])->name('kanban.obtenerTableros');
    Route::put('/kanban/{proyectoId}/tableros/{tableroId}', [KanbanController::class, 'editarTablero'])->name('kanban.editarTablero');
    Route::delete('/kanban/{proyectoId}/tableros/{tableroId}', [KanbanController::class, 'eliminarTablero'])->name('kanban.eliminarTablero');

    Route::post('/kanban/{tableroId}/columnas', [KanbanController::class, 'crearColumna'])->name('kanban.crearColumna');
    Route::get('/kanban/{tableroId}/columnas', [KanbanController::class, 'mostrarColumnas'])->name('kanban.mostrarColumnas');
    Route::delete('/kanban/columnas/{columnaId}', [KanbanController::class, 'eliminarColumna'])->name('kanban.eliminarColumna');

    Route::post('/kanban/{columnaId}/tareas', [KanbanController::class, 'crearTarea'])->name('kanban.crearTarea');
    Route::get('/kanban/{columnaId}/tareas', [KanbanController::class, 'mostrarTareas'])->name('kanban.mostrarTarea');
    Route::put('/kanban/tareas/{tareaId}/mover', [KanbanController::class, 'moveTask'])->name('kanban.moveTask');
    Route::delete('/kanban/tareas/{tareaId}', [KanbanController::class, 'eliminarTarea'])->name('kanban.eliminarTarea');
    Route::put('/kanban/tareas/{tareaId}', [KanbanController::class, 'editarTarea'])->name('kanban.editarTarea');
    Route::get('/kanban/tareas/{tareaId}', [KanbanController::class, 'obtenerTarea'])->name('kanban.obtenerTarea');
    Route::post('/kanban/tareas/{tareaId}/archivos', [KanbanController::class, 'subirArchivo'])->name('kanban.subirArchivo');
    Route::post('/kanban/tareas/{tareaId}/limite-archivo', [KanbanController::class, 'actualizarLimiteArchivo'])->name('kanban.actualizarLimiteArchivo');
    Route::post('/kanban/tareas/{tareaId}/feedback', [KanbanController::class, 'agregarFeedback'])->name('kanban.agregarFeedback');
    Route::get('/kanban/tareas/{tareaId}/feedback', [KanbanController::class, 'obtenerFeedback'])->name('kanban.obtenerFeedback');
    Route::get('/kanban/tareas/{tareaId}/archivos', [KanbanController::class, 'obtenerArchivos'])->name('kanban.obtenerArchivos');
    Route::get('/kanban/archivos/{archivoId}/descargar', [KanbanController::class, 'descargarArchivo'])->name('kanban.descargarArchivo');

    // Jitsi
    Route::get('/jitsi', function () {
        return view('jitsi');
    });
    Route::post('/jitsi/create-room', [JitsiController::class, 'createRoom']);

    // Rutas para administradores
    Route::middleware(['auth', \App\Http\Middleware\CheckRole::class.":administrador"])
            ->group(function () {
        Route::get('/users', [UsuarioController::class, 'index'])->name('index-usuarios');
        Route::get('/users/{id}/edit', [UsuarioController::class, 'edit'])->name('index-usuarios.edit');
        Route::put('/users/{id}/update', [UsuarioController::class, 'update'])->name('index-usuarios.update');
        Route::delete('/users/{id}', [UsuarioController::class, 'destroy'])->name('index-usuarios.destroy');
    });
});

require __DIR__ . '/auth.php';
