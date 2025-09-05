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

    public function enviarMensaje(string $mensaje)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-goog-api-key' => $this->api_key,
            ])->post($this->endpoint, [
                "system_instruction" => [
                    "parts" => [
                        ["text" => "Eres un asistente empresarial. Responde siempre en español, breve y claro. Usa <b>HTML</b> para negritas en lugar de **."]
                    ]
                ],
                "contents" => [
                    [
                        "parts" => [
                            ["text" => $mensaje]
                        ]
                    ]
                ],
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
            
            // Validar la estructura de la respuesta de la API
            if (!isset($json['candidates'][0]['content']['parts'][0]['text'])) {
                Log::error('Respuesta de la API con formato inesperado: ' . json_encode($json));
                return ['error' => 'Respuesta inesperada de la API.'];
            }

            // Devolver un array con los datos correctos
            return [
                'texto' => $json['candidates'][0]['content']['parts'][0]['text'],
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
