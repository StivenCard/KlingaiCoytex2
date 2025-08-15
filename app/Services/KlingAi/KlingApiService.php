<?php

namespace App\Services\KlingAi;

use Firebase\JWT\JWT;
use App\Models\ApiLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;


class KlingApiService
{
    private string $baseUrl;
    private string $accessKey;
    private string $secretKey;

    /**
     * Inicializa el servicio con los datos de configuración desde el archivo `config/services.php`.
     *
     * @throws Exception Si falta alguna clave de configuración.
     */
    public function __construct()
    {
        $this->baseUrl = config('services.kling.api_url');
        $this->accessKey = config('services.kling.access_key');
        $this->secretKey = config('services.kling.secret_key');

        if (!$this->baseUrl || !$this->accessKey || !$this->secretKey) {
            throw new Exception('KlingAI configuration missing');
        }
    }

    /**
     * Genera o recupera un token JWT válido desde la caché.
     * La clave de la caché ahora es única para cada accessKey.
     *
     * @return string Token JWT generado.
     */
    private function generateJwtToken(): string
    {
        // Usa el accessKey para generar una clave de caché única
        $cacheKey = 'klingai_token_' . md5($this->accessKey);

        return Cache::remember($cacheKey, now()->addMinutes(30), function () {
            $payload = [
                'iss' => $this->accessKey,
                'exp' => time() + 1800,
                'nbf' => time() - 5,
            ];
            return JWT::encode($payload, $this->secretKey, 'HS256');
        });
    }
    
    /**
     * Genera los headers de autorización para todas las llamadas HTTP.
     *
     * @return array Headers HTTP necesarios.
     */
    private function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->generateJwtToken(),
            'Content-Type'  => 'application/json'
        ];
    }

    /**
     * Envía una solicitud POST a la API de Kling.
     *
     * @param string $path Ruta del endpoint.
     * @param array $data Datos del cuerpo (payload).
     * @param string $type Tipo de operación (virtual_model o virtual_try_on).
     * @param string|null $humanPath Ruta a imagen humana (solo try-on).
     * @param string|null $clothPath Ruta a imagen prenda (solo try-on).
     * @return array Respuesta decodificada del JSON.
     *
     * @throws Exception Si ocurre un error de API.
     */
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

            $this->log($type, $data, $result, $path, null, $humanPath, $clothPath, 'POST');

            return $result;

        } catch (Exception $e) {
            $this->log($type, $data, null, $path, $e->getMessage(), $humanPath, $clothPath, 'POST');
            throw new Exception("KlingAI Error: " . $e->getMessage());
        }
    }

    /**
     * Realiza una solicitud GET a la API de Kling.
     *
     * @param string $path Ruta del endpoint.
     * @param string $type Tipo de operación.
     * @param string|null $humanPath Ruta humana opcional (solo try-on).
     * @param string|null $clothPath Ruta prenda opcional (solo try-on).
     * @return array Respuesta decodificada.
     *
     * @throws Exception Si la API falla.
     */
    private function get(string $path, string $type, ?string $humanPath = null, ?string $clothPath = null): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->retry(2, 1000)
                ->get($this->baseUrl . $path);

            $result = $response->json();

            if ($response->failed()) {
                throw new Exception("API Error: {$response->status()} - " . ($result['message'] ?? 'Unknown error'));
            }

            $this->log($type, [], $result, $path, null, $humanPath, $clothPath, 'GET');

            return $result;

        } catch (Exception $e) {
            $this->log($type, [], null, $path, $e->getMessage(), $humanPath, $clothPath, 'GET');
            throw new Exception("KlingAI Error: " . $e->getMessage());
        }
    }

    /**
     * Registra trazabilidad técnica de cada solicitud en la tabla api_logs.
     *
     * @param string $type Tipo de operación.
     * @param array $data Datos enviados.
     * @param array|null $result Respuesta de la API.
     * @param string $path Endpoint usado.
     * @param string|null $error Mensaje de error (si ocurre).
     * @param string|null $humanPath Ruta humana (solo try-on).
     * @param string|null $clothPath Ruta prenda (solo try-on).
     * @return void
     */
    private function log(string $type, array $data, ?array $result, string $path, ?string $error = null, ?string $humanPath = null, ?string $clothPath = null, string $httpMethod): void
    {
        $logData = [
            'operation_type' => $type,
            'task_id'        => $result['data']['task_id'] ?? null,
            'model_name'     => $data['model_name'] ?? null,
            'prompt'         => $data['prompt'] ?? null,
            'status'         => $error ? 'failed' : 'completed',
            'task_status'    => $result['data']['task_status'] ?? null,
            'response_data'  => $result,
            'error_details'  => $error ? ['message' => $error] : null,
            'endpoint'       => $this->baseUrl . $path,
            'http_method'    => $httpMethod,
        ];

        // Agregar datos específicos según el tipo de operación
        if ($type === 'virtual_model') {
            $logData['request_data'] = $data;
        } elseif ($type === 'virtual_try_on') {
            $logData['human_image_path'] = $humanPath;
            $logData['cloth_image_path'] = $clothPath;
        } elseif ($type === 'multi_image_to_video') {
            $logData['input_image_paths'] = $data['input_image_paths'] ?? null;
            // Excluimos image_list del request_data por ser muy grande
            $logData['request_data'] = array_diff_key($data, ['image_list' => true]);
        }

        ApiLog::create($logData);
    }

    /**
     * Consulta el consumo de recursos y tokens de la API
     * La clave de la caché ahora es única para cada accessKey.
     *
     * @param int|null $startTime Tiempo inicial en timestamp (ms)
     * @param int|null $endTime Tiempo final en timestamp (ms)
     * @param string|null $resourcePackName Nombre del paquete específico (opcional)
     * @return array
     */
    /**
     * Consulta el consumo de recursos y tokens de la API
     *
     * @param int|null $startTime Tiempo inicial en timestamp (ms)
     * @param int|null $endTime Tiempo final en timestamp (ms)
     * @param string|null $resourcePackName Nombre del paquete específico (opcional)
     * @return array
     */
    public function getApiConsumption($startTime = null, $endTime = null, $resourcePackName = null)
    {
        // Si no se especifica, consulta último mes
        $startTime = $startTime ?? (time() - (30 * 24 * 60 * 60)) * 1000;
        $endTime = $endTime ?? time() * 1000;

        try {
            return Cache::remember("api_consumption_{$startTime}_{$endTime}", 3600, function () use ($startTime, $endTime, $resourcePackName) {
                $response = Http::withHeaders($this->getHeaders())
                    ->timeout(30)
                    ->retry(2, 1000)
                    ->get($this->baseUrl . '/account/costs', [
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'resource_pack_name' => $resourcePackName
                    ]);

                if ($response->failed()) {
                    throw new Exception("API Error: {$response->status()} - " . ($response->json()['message'] ?? 'Unknown error'));
                }

                return $response->json();
            });
        } catch (Exception $e) {
            throw new Exception("Error consultando consumo de API: " . $e->getMessage());
        }
    }

    /**
     * Crea una tarea de generación de imagen (modelo virtual).
     *
     * @param array $data Payload con modelo_name, prompt, etc.
     * @return array Respuesta de la API.
     */
    public function createImageGenerationTask(array $data): array
    {
        return $this->post('/v1/images/generations', $data, 'virtual_model');
    }

    /**
     * Consulta el estado/resultados de una tarea de generación de imagen.
     *
     * @param string $taskId ID de la tarea.
     * @return array Resultado del servidor.
     */
    public function getImageGenerationResult(string $taskId): array
    {
        return $this->get('/v1/images/generations/' . $taskId, 'virtual_model');
    }

    /**
     * Crea una tarea de virtual try-on.
     *
     * @param array $data Payload incluyendo imágenes base64.
     * @param string|null $humanPath Ruta humana para logs.
     * @param string|null $clothPath Ruta prenda para logs.
     * @return array Respuesta de la API.
     */
    public function createVirtualTryOn(array $data, ?string $humanPath = null, ?string $clothPath = null): array
    {
        return $this->post('/v1/images/kolors-virtual-try-on', $data, 'virtual_try_on', $humanPath, $clothPath);
    }

    /**
     * Consulta resultados de una tarea de try-on.
     *
     * @param string $taskId ID de la tarea.
     * @param string|null $humanPath Ruta humana para log.
     * @param string|null $clothPath Ruta prenda para log.
     * @return array Resultado del try-on.
     */
    public function getVirtualTryOnResult(string $taskId, ?string $humanPath = null, ?string $clothPath = null): array
    {
        return $this->get('/v1/images/kolors-virtual-try-on/' . $taskId, 'virtual_try_on', $humanPath, $clothPath);
    }

    /**
     * Crea una tarea de generación de video a partir de múltiples imágenes.
     *
     * @param array $data Payload con image_list, prompt, etc.
     * @return array Respuesta de la API.
     */
    public function createMultiImageToVideo(array $data): array
    {
        return $this->post('/v1/videos/multi-image2video', $data, 'multi_image_to_video');
    }

    /**
     * Consulta el estado/resultados de una tarea de generación de video.
     *
     * @param string $taskId ID de la tarea.
     * @return array Resultado del servidor.
     */
    public function getMultiImageToVideoResult(string $taskId): array
    {
        return $this->get('/v1/videos/multi-image2video/' . $taskId, 'multi_image_to_video');
    }
}

