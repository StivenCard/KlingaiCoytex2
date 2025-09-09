<?php

namespace App\Services\ChatAi;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatAiService
{
    protected $model;
    protected $api_key;
    protected $api_url;
    protected $endpoint;

    public function __construct() {
        $this->model = config('services.chatai.model');
        $this->api_key = config('services.chatai.api_key');
        $this->api_url = config('services.chatai.api_url');
        
        $this->endpoint = "{$this->api_url}/models/{$this->model}:generateContent";
    }

    public function enviarMensaje(string $mensaje, array $historial=[])
    {
        try {

            $contents = array_merge($historial,[
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'text' => $mensaje
                        ]
                    ]
                ]
            ]);

            $tools = [
                [
                    'function_declarations' => ProcedimientosService::allProcedimientos()
                ]
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-goog-api-key' => $this->api_key,
            ])->post($this->endpoint, [
                "system_instruction" => [
                    "parts" => [
                        ["text" => "Eres un asistente empresarial. Responde siempre en español, breve y claro. Usa <b>HTML</b> para negritas en lugar de **."]
                    ]
                ],
                "contents" => $contents,
                "tools" => $tools,
                "generationConfig" => [
                    "temperature" => 0.4,
                    "thinkingConfig" => [
                        "thinkingBudget" => 0
                    ]
                ]
            ]);

            // Si la respuesta de la API no es exitosa (ej. 400, 500)
            if ($response->failed()) {
                Log::error('Error de la API de Gemini: ' . $response->body());
                return ['error' => 'Error de la API de Gemini: ' . $response->status()];
            }

            $json = $response->json();
            
            $candidate = $json['candidates'][0] ?? null;
            if (!$candidate) {
                Log::error('Respuesta de la API sin candidatos: ' . json_encode($json));
                return ['error' => 'Respuesta inesperada de la API.'];
            }

            // Manejar la respuesta que incluye una llamada a una función
            if (isset($candidate['content']['parts'][0]['functionCall'])) {
                $call = $candidate['content']['parts'][0]['functionCall'];
                return [
                    'tool_call' => [
                        'name' => $call['name'],
                        'args' => (array) $call['args'],
                    ],
                    'tokens_prompt' => $json['usageMetadata']['promptTokenCount'] ?? 0,
                    'tokens_response' => $json['usageMetadata']['candidatesTokenCount'] ?? 0,
                    'tokens_thought' => $json['usageMetadata']['thoughtsTokenCount'] ?? 0,
                    'tokens_total' => $json['usageMetadata']['totalTokenCount'] ?? 0,
                ];
            }
            
            // Respuesta de texto normal
            return [
                'texto' => $candidate['content']['parts'][0]['text'] ?? 'No se pudo generar una respuesta.',
                'tokens_prompt' => $json['usageMetadata']['promptTokenCount'] ?? 0,
                'tokens_response' => $json['usageMetadata']['candidatesTokenCount'] ?? 0,
                'tokens_thought' => $json['usageMetadata']['thoughtsTokenCount'] ?? 0,
                'tokens_total' => $json['usageMetadata']['totalTokenCount'] ?? 0,
            ];

        } catch (\Exception $error) {
            Log::error('Excepción al conectar con la API de Gemini: ' . $error->getMessage());
            return ['error' => 'Error de conexión con la API.'];
        }
    }
}
