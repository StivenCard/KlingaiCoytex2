<?php

namespace App\Services\KlingAi;

use Firebase\JWT\JWT;
use App\Models\ApiLog;
use Illuminate\Support\Facades\Http;
use Exception;

class KlingApiService
{
    protected string $baseUrl;
    protected string $accessKey;
    protected string $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('services.kling.api_url');
        $this->accessKey = config('services.kling.access_key');
        $this->secretKey = config('services.kling.secret_key');

        if (empty($this->baseUrl) || empty($this->accessKey) || empty($this->secretKey)) {
            throw new Exception('KlingAI API configuration missing');
        }
    }

    protected function generateJwtToken(): string
    {
        $headers = [
            "alg" => "HS256",
            "typ" => "JWT"
        ];

        $payload = [
            "iss" => $this->accessKey,
            "exp" => time() + 1800,
            "nbf" => time() - 5
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256', null, $headers);
    }

    protected function getAuthHeader(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->generateJwtToken(),
            'Content-Type' => 'application/json'
        ];
    }

    protected function postRequest(string $path, array $data, string $typeModel): array
    {
        try {
            $response = Http::withHeaders($this->getAuthHeader())
                ->timeout(120)
                ->retry(2, 1000)
                ->post($this->baseUrl . $path, $data);

            $json = $response->json();

            if ($response->failed()) {
                $statusCode = $response->status();
                $apiMessage = $json['message'] ?? 'Unknown API Error';
                $apiCode = $json['code'] ?? 'N/A';
                throw new Exception("API request failed: Status {$statusCode}, Code: {$apiCode}, Message: {$apiMessage}");
            }

            // ApiLog simple
            ApiLog::create([
                'operation_type' => $typeModel,
                'task_id' => $json['data']['task_id'] ?? null,
                'model_name' => $data['model_name'] ?? null,
                'prompt' => $data['prompt'] ?? null,
                'status' => 'completed',
                'task_status' => $json['data']['task_status'] ?? null,
                'request_data' => $data,
                'response_data' => $json,
                'endpoint' => $this->baseUrl . $path,
                'http_method' => 'POST',
            ]);

            return $json;

        } catch (Exception $e) {
            // ApiLog de error
            ApiLog::create([
                'operation_type' => $typeModel,
                'model_name' => $data['model_name'] ?? null,
                'prompt' => $data['prompt'] ?? null,
                'status' => 'failed',
                'request_data' => $data,
                'error_details' => ['message' => $e->getMessage()],
                'endpoint' => $this->baseUrl . $path,
                'http_method' => 'POST',
            ]);

            throw new Exception("KlingAI API Error: " . $e->getMessage());
        }
    }

    protected function getRequest(string $path, string $typeModel): array
    {
        try {
            $response = Http::withHeaders($this->getAuthHeader())
                ->timeout(30)
                ->get($this->baseUrl . $path);

            $json = $response->json();

            if ($response->failed()) {
                $statusCode = $response->status();
                $apiMessage = $json['message'] ?? 'Unknown API Error';
                $apiCode = $json['code'] ?? 'N/A';
                throw new Exception("API request failed: Status {$statusCode}, Code: {$apiCode}, Message: {$apiMessage}");
            }

            return $json;

        } catch (Exception $e) {
            throw new Exception("KlingAI API Error: " . $e->getMessage());
        }
    }

    // Virtual Model
    public function createImageGenerationTask(array $data): array
    {
        return $this->postRequest('/v1/images/generations', $data, 'virtual_model');
    }

    public function getImageGenerationResult(string $taskId): array
    {
        return $this->getRequest('/v1/images/generations/' . $taskId, 'virtual_model');
    }

    // Virtual Try-On
    public function createVirtualTryOn(array $data): array
    {
        return $this->postRequest('/v1/images/kolors-virtual-try-on', $data, 'virtual_try_on');
    }

    public function getVirtualTryOnResult(string $taskId): array
    {
        return $this->getRequest('/v1/images/kolors-virtual-try-on/' . $taskId, 'virtual_try_on');
    }
}
