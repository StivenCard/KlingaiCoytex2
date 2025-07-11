<?php

namespace App\Services\KlingAi;

use Firebase\JWT\JWT;
use App\Models\ApiLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Cache;

class KlingApiService
{
    private string $baseUrl;
    private string $accessKey;
    private string $secretKey;
    private ?string $jwtToken = null;

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
        $this->jwtToken = Cache::get('klingai.jwt_token');
        if ($this->jwtToken) {
            return $this->jwtToken;
        }

        $expiresAt = time() + 1800;

        $payload = [
            "iss" => $this->accessKey,
            "exp" => $expiresAt,
            "nbf" => time() - 5
        ];

        $this->jwtToken = JWT::encode($payload, $this->secretKey, 'HS256');

        Cache::put('klingai.jwt_token', $this->jwtToken, now()->addMinutes(30));
        return $this->jwtToken;
    }

    private function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->generateJwtToken(),
            'Content-Type' => 'application/json'
        ];
    }

    // 🔥 MODIFICADO: Recibir rutas como parámetros separados
    private function post(string $path, array $data, string $type, ?string $humanPath = null, ?string $clothPath = null): array
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

            $this->log($type, $data, $result, $path, null, $humanPath, $clothPath);
            return $result;

        } catch (Exception $e) {
            $this->log($type, $data, null, $path, $e->getMessage(), $humanPath, $clothPath);
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

    // 🔥 LOG SIMPLIFICADO: Solo rutas específicas
    private function log(string $type, array $data, ?array $result, string $path, ?string $error = null, ?string $humanPath = null, ?string $clothPath = null): void
    {
        $logData = [
            'operation_type' => $type,
            'task_id' => $result['data']['task_id'] ?? null,
            'model_name' => $data['model_name'] ?? null,
            'prompt' => $data['prompt'] ?? null,
            'status' => $error ? 'failed' : 'completed',
            'task_status' => $result['data']['task_status'] ?? null,
            'response_data' => $result,
            'error_details' => $error ? ['message' => $error] : null,
            'endpoint' => $this->baseUrl . $path,
            'http_method' => $error ? 'POST' : ($result ? 'POST' : 'GET'),
        ];

        // 🔥 LÓGICA CONDICIONAL SEGÚN TIPO
        if ($type === 'virtual_model') {
            // 🔥 VIRTUAL MODEL: Guardar todo el request_data (no hay imágenes base64)
            $logData['request_data'] = $data;

        } elseif ($type === 'virtual_try_on') {
            // 🔥 VIRTUAL TRY-ON: Solo rutas, NO request_data (evitar base64)
            $logData['human_image_path'] = $humanPath;
            $logData['cloth_image_path'] = $clothPath;
            // NO guardar request_data porque contiene base64
        }

        ApiLog::create($logData);
    }

    // 🔥 Virtual Model (solo prompt, sin rutas de imágenes)
    public function createImageGenerationTask(array $data): array
    {
        return $this->post('/v1/images/generations', $data, 'virtual_model');
    }

    public function getImageGenerationResult(string $taskId): array
    {
        return $this->get('/v1/images/generations/' . $taskId);
    }

    // 🔥 Virtual Try-On (con rutas de imágenes)
    public function createVirtualTryOn(array $data, ?string $humanPath = null, ?string $clothPath = null): array
    {
        return $this->post('/v1/images/kolors-virtual-try-on', $data, 'virtual_try_on', $humanPath, $clothPath);
    }

    public function getVirtualTryOnResult(string $taskId): array
    {
        return $this->get('/v1/images/kolors-virtual-try-on/' . $taskId);
    }
}
