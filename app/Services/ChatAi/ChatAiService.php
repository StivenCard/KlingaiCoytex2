<?php

namespace App\Services\ChatAi;

use Illuminate\Support\Facades\Http;

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

        // Construimos la URL completa para generateContent
        $this->endpoint = "{$this->api_url}/models/{$this->model}:generateContent";
    }

    public function enviarMensaje(string $mensaje){
        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'X-goog-api-key' => $this->api_key,
        ])->post($this->endpoint, [
            'contents' => [
                ['parts' => $mensaje]
            ]
        ]);

        return $response->output_text ?? 'No response';
    }
}