<?php

namespace App\Http\Controllers\ChatAi;

use App\Http\Controllers\Controller;
use App\Models\ChatAiRegistro;
use App\Services\ChatAi\ChatAiService;
use App\Services\ChatAi\ProcedimientosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatAiController extends Controller
{
    public function index()
    {
        $user_id = Auth::id() ?? '123456';
        $historial = ChatAiRegistro::where('user_id', $user_id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $historial = $historial->reverse();
        return view('chatai.chat', compact('historial'));
    }

    public function send(Request $request, ChatAiService $chatAi)
    {
        $data = $request->validate([
            'message' => 'required|string|max:3000',
        ]);

        try {
            $user_id = Auth::id() ?? '123456';
            $historial = ChatAiRegistro::where('user_id', $user_id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $mensajesHistorial = [];
            foreach ($historial as $registro) {
                $mensajesHistorial[] = ['role' => 'user', 'parts' => [['text' => $registro->user_message]]];
                $mensajesHistorial[] = ['role' => 'model', 'parts' => [['text' => $registro->ai_response]]];
            }

            //Primer mensaje a Gemini
            $reply = $chatAi->enviarMensaje($data['message'], $mensajesHistorial);

            //Si Gemini pide ejecutar función
            if (isset($reply['tool_call'])) {
                $toolName = $reply['tool_call']['name'];
                $toolArgs = $reply['tool_call']['args'];

                Log::info("IA solicitó función: {$toolName}", $toolArgs);

                $dbResult = ProcedimientosService::ejecutarProcedimiento($toolName, $toolArgs);

                //Log de depuración del payload que enviaremos de vuelta a Gemini
                Log::debug("Payload enviado a Gemini con functionResponse", [
                    'toolName' => $toolName,
                    'dbResult' => $dbResult
                ]);

                //Segunda llamada con resultado del procedimiento
                $mensajesCompleto = array_merge($mensajesHistorial, [
                    ['role' => 'user', 'parts' => [['text' => $data['message']]]],
                    ['role' => 'model', 'parts' => [[
                        'functionCall' => ['name' => $toolName, 'args' => $toolArgs]
                    ]]],
                    ['role' => 'tool', 'parts' => [[
                        'functionResponse' => [
                            'name' => $toolName,
                            'response' => ['data' => $dbResult ?: []] //siempre objeto con data
                        ]
                    ]]]
                ]);

                $finalReply = $chatAi->enviarMensaje(
                    $data['message'],
                    $mensajesCompleto
                );

                $reply = $finalReply;
            }

            if (isset($reply['error'])) {
                return response()->json(['error' => $reply['error']], 500);
            }

            $price = ChatAiRegistro::calculatePrice(
                $reply['tokens_prompt'],
                $reply['tokens_response']
            );

            ChatAiRegistro::create([
                'user_id' => $user_id,
                'user_message' => $data['message'],
                'ai_response' => $reply['texto'] ?? '',
                'tokens_prompt' => $reply['tokens_prompt'],
                'tokens_thought' => $reply['tokens_thought'],
                'tokens_response' => $reply['tokens_response'],
                'tokens_total' => $reply['tokens_total'],
                'price' => $price
            ]);

            return response()->json([
                'user' => $data['message'],
                'assistant' => $reply['texto'] ?? ''
            ]);

        } catch (\Exception $e) {
            Log::error('Error ChatAI: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error inesperado.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
