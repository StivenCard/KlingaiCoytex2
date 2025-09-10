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

    public function __construct()
    {
        $this->model = config('services.chatai.model');
        $this->api_key = config('services.chatai.api_key');
        $this->api_url = config('services.chatai.api_url');
        $this->endpoint = "{$this->api_url}/models/{$this->model}:generateContent";
    }

    public function enviarMensaje(string $mensaje, array $historial = [])
    {
        try {
            $contents = array_merge($historial, [
                [
                    'role' => 'user',
                    'parts' => [['text' => $mensaje]]
                ]
            ]);

            $tools = [
                ['functionDeclarations' => ProcedimientosService::allProcedimientos()]
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $this->api_key,
            ])->post($this->endpoint, [
                "systemInstruction" => [
                    "parts" => [
                        ["text" => "Eres un asistente. Responde en español, breve y claro. Usa <b>HTML</b> para negritas en vez de **.**. Usa <ul><li>HTML</li></ul> para listas. Si requieres mas información, pide que el usuario la proporcione. Si la pregunta corresponde a un procedimiento de base de datos, usa las herramientas declaradas. Si no corresponde a un procedimiento, responde con tu conocimiento general de forma natural, breve y clara."],
                    ]
                ],
                "contents" => $contents,
                "tools" => $tools,
                "generationConfig" => [
                    "temperature" => 0.4,
                    "thinkingConfig" => ["thinkingBudget" => 0]
                ]
            ]);

            if ($response->failed()) {
                Log::error('Error API Gemini: ' . $response->body());
                return ['error' => 'Error de la API de Gemini'];
            }

            $json = $response->json();
            $candidate = $json['candidates'][0] ?? null;
            if (!$candidate) {
                return ['error' => 'Respuesta inesperada de la API.'];
            }

            //Si es llamada a función
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

            // Respuesta de texto
            return [
                'texto' => $candidate['content']['parts'][0]['text'] ?? 'No se pudo generar respuesta.',
                'tokens_prompt' => $json['usageMetadata']['promptTokenCount'] ?? 0,
                'tokens_response' => $json['usageMetadata']['candidatesTokenCount'] ?? 0,
                'tokens_thought' => $json['usageMetadata']['thoughtsTokenCount'] ?? 0,
                'tokens_total' => $json['usageMetadata']['totalTokenCount'] ?? 0,
            ];

        } catch (\Exception $e) {
            Log::error('Excepción API Gemini: ' . $e->getMessage());
            return ['error' => 'Error de conexión con la API.'];
        }
    }
}
