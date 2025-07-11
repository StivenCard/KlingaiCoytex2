<?php

namespace App\Services\KlingAi;

use Firebase\JWT\JWT;
use App\Models\ApiLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class KlingApiService
{
    private string $baseUrl;
    private string $accessKey;
    private string $secretKey;
    private ?string $jwtToken = null;
    private int $jwtExpiresAt = 0;

    public function __construct()
    {
        $this->baseUrl = config('services.kling.api_url');
        $this->accessKey = config('services.kling.access_key');
        $this->secretKey = config('services.kling.secret_key');

        if (!$this->baseUrl || !$this->accessKey || !$this->secretKey) {
            throw new Exception('KlingAI configuration missing');
        }
    }

    private function generateJwtToken(): string
    {
        if ($this->jwtToken && time() < $this->jwtExpiresAt) {
            return $this->jwtToken;
        }

        $expiresAt = time() + 1800; // 30 minutos

        $payload = [
            "iss" => $this->accessKey,
            "exp" => $expiresAt,
            "nbf" => time() - 5
        ];

        $this->jwtToken = JWT::encode($payload, $this->secretKey, 'HS256');
        $this->jwtExpiresAt = $expiresAt;

        Log::debug('JWT Token generado', [
            'expires_at' => date('Y-m-d H:i:s', $this->jwtExpiresAt),
            'iss' => $this->accessKey
        ]);

        return $this->jwtToken;
    }

    private function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->generateJwtToken(),
            'Content-Type' => 'application/json'
        ];
    }

    private function post(string $path, array $data, string $type): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(120)
                ->retry(2, 1000)
                ->post($this->baseUrl . $path, $data);

            $result = $response->json();

            if ($response->failed()) {
                throw new Exception("API Error: {$response->status()} - " . ($result['message'] ?? 'Unknown error'));
            }

            $this->log($type, $data, $result, $path);
            return $result;

        } catch (Exception $e) {
            $this->log($type, $data, null, $path, $e->getMessage());
            throw new Exception("KlingAI Error: " . $e->getMessage());
        }
    }

    private function get(string $path): array
    {
        $response = Http::withHeaders($this->getHeaders())
            ->timeout(30)
            ->get($this->baseUrl . $path);

        $result = $response->json();

        if ($response->failed()) {
            throw new Exception("API Error: {$response->status()} - " . ($result['message'] ?? 'Unknown error'));
        }

        return $result;
    }

    private function log(string $type, array $data, ?array $result, string $path, ?string $error = null): void
    {
        ApiLog::create([
            'operation_type' => $type,
            'task_id' => $result['data']['task_id'] ?? null,
            'model_name' => $data['model_name'] ?? null,
            'prompt' => $data['prompt'] ?? null,
            'status' => $error ? 'failed' : 'completed',
            'task_status' => $result['data']['task_status'] ?? null,
            'request_data' => $data,
            'response_data' => $result,
            'error_details' => $error ? ['message' => $error] : null,
            'endpoint' => $this->baseUrl . $path,
            'http_method' => $error ? 'POST' : ($result ? 'POST' : 'GET'),
        ]);
    }

    /**
     * Limpiar token manualmente (útil para testing o errores de auth)
     */
    public function clearToken(): void
    {
        $this->jwtToken = null;
        $this->jwtExpiresAt = 0;
    }

    // Virtual Model
    public function createImageGenerationTask(array $data): array
    {
        return $this->post('/v1/images/generations', $data, 'virtual_model');
    }

    public function getImageGenerationResult(string $taskId): array
    {
        return $this->get('/v1/images/generations/' . $taskId);
    }

    // Virtual Try-On
    public function createVirtualTryOn(array $data): array
    {
        return $this->post('/v1/images/kolors-virtual-try-on', $data, 'virtual_try_on');
    }

    public function getVirtualTryOnResult(string $taskId): array
    {
        return $this->get('/v1/images/kolors-virtual-try-on/' . $taskId);
    }
}
