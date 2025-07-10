<?php

namespace App\Services\KlingAi;

use Illuminate\Support\Facades\Http;

class KlingApiService
{
    protected $authService;

    public function __construct(KlingAuthService $authService)
    {
        $this->authService = $authService;
    }

    public function virtualTryOn(array $data)
    {
        $token = $this->authService->generateToken();
        $url = config('services.kling.api_url') . '/v1/images/kolors-virtual-try-on';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->post($url, $data);

        return $response->json();
    }

    public function checkTaskStatus(string $taskId)
    {
        $token = $this->authService->generateToken();
        $url = config('services.kling.api_url') . '/v1/images/kolors-virtual-try-on/' . $taskId;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->get($url);

        return $response->json();
    }
}
