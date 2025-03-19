<?php

namespace App\Http\Controllers;

use App\Services\FeedbackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    protected $feedbackService;

    public function __construct(FeedbackService $feedbackService)
    {
        $this->feedbackService = $feedbackService;
    }

    /**
     * Agrega un feedback a una tarea.
     */
    public function agregarFeedback(Request $request, int $tareaId)
    {
        $request->validate([
            'comentario' => 'required|string|max:1000',
            'archivo' => 'nullable|file',
        ]);

        try {
            $feedback = $this->feedbackService->agregarFeedback(
                $tareaId,
                Auth::id(), // Usando Auth::id()
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
                'mensaje' => 'Error al agregar feedback: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene el historial de feedbacks de una tarea.
     */
    public function obtenerFeedback(int $tareaId)
    {
        try {
            $feedback = $this->feedbackService->obtenerFeedback($tareaId);
            return response()->json(['success' => true, 'feedback' => $feedback]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el feedback: ' . $e->getMessage(),
            ], 500);
        }
    }
}
