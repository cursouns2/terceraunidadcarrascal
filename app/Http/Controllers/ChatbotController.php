<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function getResponse(Request $request)
    {
        Log::info('ChatbotController: Inicio de getResponse');
        // Validar la solicitud
        $request->validate([
            'message' => 'required|string',
        ]);

        // Llamar al chatbot.php (asegúrate de que la ruta sea correcta)
        $userMessage = $request->input('message');
        Log::info('ChatbotController: Mensaje del usuario: ' . $userMessage);
        // Agrega tu clave de API de Google Gemini
        $API_KEY = config('services.gemini.api_key');  // ❗ Usa la clave desde la configuración
        Log::info('ChatbotController: API Key: ' . substr($API_KEY, 0, 5) . '...');

        // Preparar la solicitud a la API de Gemini
        $payload = [
            "contents" => [
                ["parts" => [["text" => $userMessage]]]
            ]
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $API_KEY,  // URL corregida
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_SSL_VERIFYPEER => false,  // Desactiva la verificación del certificado (SOLO para desarrollo)
            CURLOPT_SSL_VERIFYHOST => false,  // Desactiva la verificación del host (SOLO para desarrollo)
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl); // Obtener el mensaje de error de cURL
        $errno = curl_errno($curl); // Obtener el código de error de cURL

        if ($errno) {
            Log::error('ChatbotController: Error de cURL (' . $errno . '): ' . $error);
            curl_close($curl);
            return response()->json(["error" => "Error de cURL: " . $error], 500);
        }
        curl_close($curl);

        Log::info('ChatbotController: Respuesta de cURL: ' . $response);

        // Verificar errores en la API de Gemini
        if (!$response) {
            Log::error('ChatbotController: No se recibió respuesta de Gemini');
            return response()->json(["error" => "No se recibió respuesta de Gemini"], 500);
        }

        $response = json_decode($response, true);

        // Verificar si la respuesta contiene datos
        if (!isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            Log::error('ChatbotController: Respuesta inválida de Gemini: ' . json_encode($response));
            return response()->json(["error" => "Respuesta inválida de Gemini"], 500);
        }

        // Extraer la respuesta generada por Gemini
        $chatbotResponse = $response['candidates'][0]['content']['parts'][0]['text'];
        Log::info('ChatbotController: Respuesta del chatbot: ' . $chatbotResponse);

        // Construir la respuesta en formato JSON
        return response()->json(["message" => $chatbotResponse]);
    }
}
