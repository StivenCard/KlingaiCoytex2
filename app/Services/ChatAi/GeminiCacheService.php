<?php

namespace App\Services\ChatAi;

class GeminiCacheService {

    protected $modelo;
    protected $api_key;
    protected $api_url;

    public function __construct() {
        $this->modelo = config('services.chatai.model');
        $this->api_key = config('services.chatai.api_key');
        $this->api_url = config('services.chatai.api_url');
    }

    public function crearCache(){

    }
}