<?php

namespace App\Services\ChatAi;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiCacheService
{
    protected $api_key;
    protected $api_url;
    protected $model;

    public function __construct()
    {
        $this->api_key = config('services.chatai.api_key');
        $this->api_url = config('services.chatai.api_url');
        $this->model   = config('services.chatai.model');
    }

    /**
     * Crea el cache en Gemini con systemInstruction y tools.
     */
    public function crearCache()
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $this->api_key,
            ])->post("{$this->api_url}/cachedContents", [
                "model" => "models/{$this->model}",
                "systemInstruction" => [
                    "parts" => [[
                        "text" => "Eres un asistente. Responde en español, breve y claro.
                        Si requieres más información, pide que el usuario la proporcione.
                        Si el usuario usa expresiones ambiguas como 'y cuántos son', 'y ahora',
                        'el último', etc., asúmelas en relación con lo respondido anteriormente.
                        Si la pregunta corresponde a un procedimiento de base de datos, usa las
                        herramientas declaradas. Si no corresponde, responde con tu conocimiento
                        general de forma natural."
                    ]]
                ],
                "tools" => [
                    ["functionDeclarations" => ProcedimientosService::allProcedimientos()]
                ],
                "ttl" => "3600s" // 1 hora
            ]);

            if ($response->failed()) {
                Log::error("Error creando cache Gemini: " . $response->body());
                return null;
            }

            $json = $response->json();
            return $json['name'] ?? null;
        } catch (\Exception $e) {
            Log::error("Excepción creando cache Gemini: " . $e->getMessage());
            return null;
        }
    }
}
