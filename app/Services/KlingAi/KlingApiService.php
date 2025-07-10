<?php

namespace App\Services\KlingAi;

use Illuminate\Support\Facades\Http;
use Firebase\JWT\JWT;

class KlingApiService
{
    protected ?string $currentToken = null;
    protected int $tokenExpiresAt = 0;

    protected function generateNewToken(): void
    {
        $ak = config('services.kling.access_key');
        $sk = config('services.kling.secret_key');

        $expirationTime = time() + 1800; // 30 minutos desde ahora

        $payload = [
            'iss' => $ak,
            'exp' => $expirationTime,
            'nbf' => time() - 5
        ];

        $this->currentToken = JWT::encode($payload, $sk, 'HS256');
        $this->tokenExpiresAt = $expirationTime;
    }

    protected function getValidToken(): string
    {
        // Solo generar nuevo token si:
        // 1. No existe token actual, O
        // 2. El token actual ya expiró (con margen de 60 segundos)
        if ($this->currentToken === null || time() >= ($this->tokenExpiresAt - 60)) {
            $this->generateNewToken();
        }

        // Reutilizar el token existente
        return $this->currentToken;
    }


    public function virtualTryOn(array $data)
    {
        $token = $this->getValidToken();
        $url = config('services.kling.api_url') . '/v1/images/kolors-virtual-try-on';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->post($url, $data);

        return $response->json();
    }

    public function checkTaskStatus(string $taskId)
    {
        $token = $this->getValidToken();
        $url = config('services.kling.api_url') . '/v1/images/kolors-virtual-try-on/' . $taskId;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->get($url);

        return $response->json();
    }
}

