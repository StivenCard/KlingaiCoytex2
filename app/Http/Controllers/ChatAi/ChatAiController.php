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
            $historial = ChatAiRegistro::where('user_id', $user_id)->orderBy('created_at', 'asc')->limit(10)->get();

            $mensajesHistorial = [];
            foreach ($historial as $registro) {
                $mensajesHistorial[] = ['role' => 'user', 'parts' => [['text' => $registro->user_message]]];
                $mensajesHistorial[] = ['role' => 'model', 'parts' => [['text' => $registro->ai_response]]];
            }

            $reply = $chatAi->enviarMensaje($data['message'], $mensajesHistorial);
            
            if (isset($reply['tool_call'])) {
                $toolCall = $reply['tool_call'];
                $toolName = $toolCall['name'];
                $toolArgs = $toolCall['args'];
                
                Log::info("La IA solicitó una llamada a la función: {$toolName}");
                
                $dbReply = ProcedimientosService::ejecutarProcedimiento($toolName, $toolArgs);

                // 🟢 CAMBIO: Si la ejecución del procedimiento devuelve un error, lo mostramos directamente.
                if (isset($dbReply['error'])) {
                    return response()->json(['error' => $dbReply['error']], 500);
                }

                $mensajesHistorial[] = ['role' => 'user', 'parts' => [['text' => $data['message']]]];
                $mensajesHistorial[] = ['role' => 'model', 'parts' => [['functionCall' => ['name' => $toolName, 'args' => $toolArgs]]]];
                $mensajesHistorial[] = ['role' => 'tool', 'parts' => [['functionResponse' => ['name' => $toolName, 'response' => json_encode($dbReply)]]]];
                
                $finalReply = $chatAi->enviarMensaje($data['message'], $mensajesHistorial);
                $reply = $finalReply;
            }

            if (isset($reply['error'])) {
                return response()->json(['error' => $reply['error']], 500);
            }
            
            $price = ChatAiRegistro::calculatePrice($reply['tokens_prompt'], $reply['tokens_response']);
            
            $trasabilidad = [
                'user_id' => Auth::id() ?? '123456',
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
            Log::error('Error en el controlador al enviar mensaje al ChatAI: ' . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error inesperado. Por favor, inténtalo de nuevo.', 'details' => $e->getMessage()], 500);
        }
    }
}
