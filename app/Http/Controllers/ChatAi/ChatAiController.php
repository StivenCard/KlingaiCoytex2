<?php

namespace App\Http\Controllers\ChatAi;

use App\Http\Controllers\Controller;
use App\Models\ChatAiRegistro;
use App\Services\ChatAi\ChatAiService;
use App\Services\ChatAi\ProcedimientosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ChatAiController extends Controller
{
    public function index()
    {
        $user_id = Auth::id() ?? '123456';
        $historial = ChatAiRegistro::where('user_id', $user_id)->orderBy('created_at', 'asc')->limit(10)->get();

        return view('chatai.chat', compact('historial'));
    }

    public function send(Request $request, ChatAiService $chatAi)
    {
        $data = $request->validate([
            'message' => 'required|string|max:3000',
        ]);

        try {
            $user_id = Auth::id() ?? '123456';
            $historial = ChatAiRegistro::where('user_id', $user_id)->orderBy('created_at', 'desc')->limit(10)->get();

            // Construir historial de mensajes en formato correcto para Gemini
            $mensajesHistorial = [];
            foreach ($historial as $registro) {
                $mensajesHistorial[] = ['role' => 'user', 'parts' => [['text' => $registro->user_message]]];
                $mensajesHistorial[] = ['role' => 'model', 'parts' => [['text' => $registro->ai_response]]];
            }

            // Primera llamada a la IA
            $reply = $chatAi->enviarMensaje($data['message'], $mensajesHistorial);
            
            if (isset($reply['tool_call'])) {
                $toolCall = $reply['tool_call'];
                $toolName = $toolCall['name'];
                $toolArgs = $toolCall['args'];
                
                Log::info("La IA solicitó una llamada a la función: {$toolName}", $toolArgs);
                
                // Ejecutar el procedimiento almacenado
                $dbResult = ProcedimientosService::ejecutarProcedimiento($toolName, $toolArgs);

                // Construir el historial completo con la función llamada
                $mensajesCompleto = array_merge($mensajesHistorial, [
                    // Mensaje del usuario
                    ['role' => 'user', 'parts' => [['text' => $data['message']]]],
                    
                    // Respuesta de la IA con la llamada a función
                    ['role' => 'model', 'parts' => [['functionCall' => ['name' => $toolName, 'args' => $toolArgs]]]],
                    
                    // Respuesta de la función
                    ['role' => 'user', 'parts' => [['functionResponse' => [
                        'name' => $toolName,
                        'response' => $dbResult
                    ]]]]
                ]);
                
                // Segunda llamada a la IA con el resultado de la función
                $finalReply = $chatAi->enviarMensaje("Por favor analiza y presenta la información de manera clara y útil.", $mensajesCompleto);
                
                if (isset($finalReply['error'])) {
                    // Si hay error en la segunda llamada, usar la primera respuesta
                    return response()->json(['error' => $finalReply['error']], 500);
                }
                
                $reply = $finalReply; // Usar la respuesta final que incluye el análisis de los datos
            }

            if (isset($reply['error'])) {
                return response()->json(['error' => $reply['error']], 500);
            }
            
            $price = ChatAiRegistro::calculatePrice($reply['tokens_prompt'], $reply['tokens_response']);
            
            $trazabilidad = [
                'user_id' => $user_id,
                'user_message' => $data['message'],
                'ai_response' => $reply['texto'],
                'tokens_prompt' => $reply['tokens_prompt'],
                'tokens_thought' => $reply['tokens_thought'],
                'tokens_response' => $reply['tokens_response'],
                'tokens_total' => $reply['tokens_total'],
                'price' => $price
            ];
            
            ChatAiRegistro::create($trazabilidad);

            return response()->json([
                'user' => $data['message'],
                'assistant' => $reply['texto']
            ]);

        } catch (\Exception $e) {
            Log::error('Error en el controlador al enviar mensaje al ChatAI: ' . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error inesperado. Por favor, inténtalo de nuevo.', 'details' => $e->getMessage()], 500);
        }
    }
}