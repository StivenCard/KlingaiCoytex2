<?php

namespace App\Services\KlingAi;

use Firebase\JWT\JWT;

class KlingAuthService
{
    public function generateToken()
    {
        $ak = config('services.kling.access_key');
        $sk = config('services.kling.secret_key');

        $payload = [
            'iss' => $ak,
            'exp' => time() + 1800,
            'nbf' => time() - 5
        ];

        return JWT::encode($payload, $sk, 'HS256');
    }
}
