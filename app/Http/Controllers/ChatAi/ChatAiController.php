<?php

namespace App\Http\Controllers\ChatAi;

use App\Http\Controllers\Controller;
use App\Models\ChatAiRegistro;
use App\Services\ChatAi\ChatAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ChatAiController extends Controller
{
    public function index()
    {
        return view('chatai.chat');
    }

    public function send(Request $request, ChatAiService $chatAi)
    {
        $data = $request->validate([
            'message' => 'required|string|max:3000',
        ]);

        try {
            $reply = $chatAi->enviarMensaje($data['message']);

            // Verificar si el servicio devolvió un error
            if (isset($reply['error'])) {
                // Devolver el error al frontend
                return response()->json([
                    'error' => $reply['error']
                ], 500);
            }
            
            $price = ChatAiRegistro::calculatePrice($reply['tokens_prompt'], $reply['tokens_response']);
            
            // Guardar en la tabla de trazabilidad
            $trasabilidad = [
                'user_id' => Auth::id() ?? '123456', // Obtiene el ID del usuario autenticado o 'anonimo'
                'user_message' => $data['message'],
                'ai_response' => $reply['texto'],
                'tokens_prompt' => $reply['tokens_prompt'],
                'tokens_thought' => $reply['tokens_thought'],
                'tokens_response' => $reply['tokens_response'],
                'tokens_total' => $reply['tokens_total'],
                'price' => $price
            ];
            
            ChatAiRegistro::create($trasabilidad);

            return response()->json([
                'user' => $data['message'],
                'assistant' => $reply['texto']
            ]);

        } catch (\Exception $e) {
            // Se registra el error para un seguimiento detallado en el backend
            Log::error('Error en el controlador al enviar mensaje al ChatAI: ' . $e->getMessage());
            return response()->json([
                'error' => 'Ocurrió un error inesperado. Por favor, inténtalo de nuevo.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
