<?php

namespace App\Http\Controllers\ChatAi;

use App\Http\Controllers\Controller;
use App\Models\ChatAiRegistro;
use App\Services\ChatAi\ChatAiService;
use App\Services\ChatAi\ProcedimientosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use stdClass;

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
                ->limit(5)
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
                        'functionCall' => ['name' => $toolName, 'args' => empty($toolArgs) ? new stdClass() : $toolArgs]
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
                'user_message' => $this->markdownToHtml($data['message']),
                'ai_response' => $this->markdownToHtml($reply['texto'] ?? ''),
                'tokens_prompt' => $reply['tokens_prompt'],
                'tokens_thought' => $reply['tokens_thought'],
                'tokens_response' => $reply['tokens_response'],
                'tokens_total' => $reply['tokens_total'],
                'price' => $price
            ]);

            return response()->json([
                'user' => $this->markdownToHtml($data['message']),
                'assistant' => $this->markdownToHtml($reply['texto'] ?? '')
            ]);

        } catch (\Exception $e) {
            Log::error('Error ChatAI: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error inesperado.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    private function markdownToHtml(string $text): string
    {
        // Saltos de línea por <br>
        $text = nl2br($text);
        // Negritas **texto**
        $text = preg_replace('/\*\*(.*?)\*\*/', '<b>$1</b>', $text);
        // Cursivas *texto* o _texto_
        $text = preg_replace('/(\*|_)(.*?)\1/', '<i>$2</i>', $text);
        // Títulos h1 # título (linea que inicia con # )
        $text = preg_replace('/^# (.*)$/m', '<h1>$1</h1>', $text);
        // Títulos h2 ## título
        $text = preg_replace('/^## (.*)$/m', '<h2>$1</h2>', $text);
        // Títulos h3 ### título
        $text = preg_replace('/^### (.*)$/m', '<h3>$1</h3>', $text);
        // Listas con guion, asterisco o más (-, * o +)
        $text = preg_replace_callback('/(^|\n)(\s*[-*+] .+(\n|$))+/m', function ($matches) {
            $lines = preg_split('/\n/', trim($matches[0]));
            $html = '<ul style="list-style-type: none; padding-left: 0;">';
            foreach ($lines as $line) {
                $line = preg_replace('/^\s*[-*+] /', '', $line);
                $html .= '<li>' . trim($line) . '</li>';
            }
            $html .= '</ul>';
            return $html;
        }, $text);
        // Lista con viñeta •
        $text = preg_replace_callback('/(^|\n)(• .+(\n|$))+/m', function ($matches) {
            $lines = preg_split('/\n/', trim($matches[0]));
            $html = '<ul style="list-style-type: none; padding-left: 0;">';
            foreach ($lines as $line) {
                $line = preg_replace('/^• /', '', $line);
                $html .= '<li>' . trim($line) . '</li>';
            }
            $html .= '</ul>';
            return $html;
        }, $text);
        // Convertir enlaces [texto](url)
        $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $text);
        // Código en línea `codigo`
        $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
        // Bloques de código con triple backtick ``````
        $text = preg_replace('/``````/s', '<pre><code>$1</code></pre>', $text);

        return $text;
    }

}

