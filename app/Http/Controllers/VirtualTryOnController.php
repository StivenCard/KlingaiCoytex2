<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\KlingAi\KlingApiService;
use App\Services\KlingAi\ImageProcessingService;
use App\Models\VirtualTryOn;
use App\Models\ApiLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VirtualTryOnController extends Controller
{
    protected $apiService;
    protected $imageService;

    public function __construct(KlingApiService $apiService, ImageProcessingService $imageService)
    {
        $this->apiService = $apiService;
        $this->imageService = $imageService;
    }

    public function show()
    {
        $defaultModels = $this->getDefaultModels();

        $existingTryOns = VirtualTryOn::orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->map(function($tryon) {
                return [
                    'id' => $tryon->id,
                    'task_id' => $tryon->task_id,
                    'model_type' => $tryon->model_type,
                    'garments_type' => $tryon->garments_type,
                    'status' => $this->mapInternalStatusToDisplay($tryon->status),
                    'created_at' => $tryon->created_at->toISOString(),
                    'result_image_paths' => $tryon->result_image_paths ?
                        array_map(function($path) {
                            return Storage::url($path);
                        }, $tryon->result_image_paths) : []
                ];
            });

        return view('virtual-try-on', compact('defaultModels', 'existingTryOns'));
    }

    private function mapInternalStatusToDisplay(string $internalStatus): string
    {
        return match($internalStatus) {
            'submitted', 'processing' => 'processing',
            'completed' => 'completed',
            'failed' => 'failed',
            default => 'processing'
        };
    }

    public function generate(Request $request)
    {
        $request->validate([
            'model_source' => 'required|in:virtual,default,upload',
            'selected_default_model' => 'required_if:model_source,default|string',
            'human_image' => 'required_if:model_source,upload|file|image|max:10240',
            'garment_type' => 'required|in:single,multiple',
            'single_garment' => 'required_if:garment_type,single|file|image|max:10240',
            'top_garment' => 'required_if:garment_type,multiple|file|image|max:10240',
            'bottom_garment' => 'required_if:garment_type,multiple|file|image|max:10240',
            'output_count' => 'required|integer|min:1|max:4',
        ]);

        try {
            $storedPaths = $this->saveInputImages($request);
            $humanImage = $this->processHumanImage($request);
            $clothImage = $this->processGarments($request);

            $payload = [
                'model_name' => 'kolors-virtual-try-on-v1-5',
                'human_image' => $humanImage,
                'cloth_image' => $clothImage,
                'n' => (int) $request->input('output_count', 1),
            ];

            $response = $this->apiService->virtualTryOn($payload);

            if (isset($response['data']['task_id'])) {
                $this->createVirtualTryOnRecord($request, $response, $storedPaths);
                $this->createApiLogRecord($request, $response, $payload);
            }

            Log::info('Virtual Try-On Request', [
                'task_id' => $response['data']['task_id'] ?? null,
                'model_source' => $request->input('model_source'),
                'garment_type' => $request->input('garment_type'),
                'stored_paths' => $storedPaths,
                'response_code' => $response['code'] ?? null,
                'timestamp' => now(),
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            $this->createApiLogRecord($request, null, [], $e);

            Log::error('Virtual Try-On Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function taskStatus($taskId)
    {
        try {
            // Registrar GET en ApiLog ANTES de la llamada
            $this->createApiLogRecordForGet($taskId);

            $response = $this->apiService->checkTaskStatus($taskId);

            // Actualizar registro VirtualTryOn
            $this->updateVirtualTryOnRecord($taskId, $response);

            // Actualizar ApiLog GET con respuesta
            $this->updateApiLogRecordForGet($taskId, $response);

            // 🔥 NUEVO: Actualizar registro POST original cuando termina
            if (isset($response['data']['task_status']) && in_array($response['data']['task_status'], ['succeed', 'failed'])) {
                $this->updateOriginalApiLogRecord($taskId, $response);
            }

            // Descargar y guardar resultados si están listos
            if (isset($response['data']['task_status']) && $response['data']['task_status'] === 'succeed') {
                $savedResults = $this->saveResultImages($response['data']['task_result']['images'] ?? []);
                $this->updateResultPaths($taskId, $savedResults);

                Log::info('Virtual Try-On Results Saved', [
                    'task_id' => $taskId,
                    'saved_results' => $savedResults,
                    'timestamp' => now(),
                ]);
            }

            Log::info('Virtual Try-On Status Check', [
                'task_id' => $taskId,
                'status' => $response['data']['task_status'] ?? 'unknown',
                'has_results' => isset($response['data']['task_result']),
                'timestamp' => now(),
            ]);

            return response()->json($response);
        } catch (\Exception $e) {
            $this->updateApiLogRecordForGet($taskId, null, $e);

            Log::error('Virtual Try-On Status Error', [
                'task_id' => $taskId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    // 🔥 NUEVO: Actualizar registro POST original
    private function updateOriginalApiLogRecord(string $taskId, array $response): void
    {
        try {
            // Buscar el registro POST original (el primero creado para este task_id)
            $originalApiLog = ApiLog::where('task_id', $taskId)
                ->where('http_method', 'POST')
                ->where('operation_type', 'virtual_try_on')
                ->orderBy('created_at', 'asc') // El más antiguo (original)
                ->first();

            if ($originalApiLog) {
                $finalStatus = $this->mapKlingStatus($response['data']['task_status'] ?? 'failed');

                $originalApiLog->update([
                    'status' => $finalStatus,
                    'task_status' => $response['data']['task_status'] ?? null,
                    'task_status_msg' => $response['data']['task_status_msg'] ?? null,
                    'updated_at' => now(),
                ]);

                Log::info('ApiLog POST original updated to final status', [
                    'user' => 'StivenCard',
                    'task_id' => $taskId,
                    'original_status' => $originalApiLog->getOriginal('status'),
                    'final_status' => $finalStatus,
                    'api_log_id' => $originalApiLog->id,
                    'timestamp' => now(),
                ]);
            } else {
                Log::warning('Original ApiLog POST record not found for update', [
                    'user' => 'StivenCard',
                    'task_id' => $taskId,
                    'timestamp' => now(),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to update original ApiLog POST record', [
                'user' => 'StivenCard',
                'task_id' => $taskId,
                'error' => $e->getMessage(),
                'timestamp' => now(),
            ]);
        }
    }

    // Crear registro ApiLog para GET (antes de llamada)
    private function createApiLogRecordForGet(string $taskId): void
    {
        try {
            ApiLog::create([
                'operation_type' => 'virtual_try_on',
                'task_id' => $taskId,
                'model_name' => 'kolors-virtual-try-on-v1-5',
                'prompt' => null,
                'hints_used' => null,
                'status' => 'processing',
                'task_status' => null,
                'task_status_msg' => null,
                'request_data' => [
                    'task_id' => $taskId,
                    'operation' => 'status_check',
                    'method' => 'GET',
                ],
                'response_data' => null,
                'error_details' => null,
                'endpoint' => '/v1/images/kolors-virtual-try-on/' . $taskId,
                'http_method' => 'GET',
            ]);

            Log::info('ApiLog GET record created', [
                'user' => 'StivenCard',
                'task_id' => $taskId,
                'operation' => 'status_check',
                'timestamp' => now(),
            ]);

        } catch (\Exception $e) {
            Log::warning('Failed to create ApiLog GET record', [
                'user' => 'StivenCard',
                'task_id' => $taskId,
                'error' => $e->getMessage()
            ]);
        }
    }

    // Actualizar registro ApiLog para GET (después de respuesta)
    private function updateApiLogRecordForGet(string $taskId, ?array $response, ?\Exception $error = null): void
    {
        try {
            $apiLogRecord = ApiLog::where('task_id', $taskId)
                ->where('http_method', 'GET')
                ->where('response_data', null)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($apiLogRecord) {
                $apiLogRecord->update([
                    'status' => $error ? 'failed' : ($response['data']['task_status'] ?? 'processing'),
                    'task_status' => $response['data']['task_status'] ?? null,
                    'task_status_msg' => $response['data']['task_status_msg'] ?? null,
                    'response_data' => $response,
                    'error_details' => $error ? [
                        'message' => $error->getMessage(),
                        'file' => $error->getFile(),
                        'line' => $error->getLine(),
                    ] : null,
                ]);

                Log::info('ApiLog GET record updated', [
                    'user' => 'StivenCard',
                    'task_id' => $taskId,
                    'final_status' => $apiLogRecord->status,
                    'has_error' => $error !== null,
                    'timestamp' => now(),
                ]);
            }

        } catch (\Exception $e) {
            Log::warning('Failed to update ApiLog GET record', [
                'user' => 'StivenCard',
                'task_id' => $taskId,
                'error' => $e->getMessage()
            ]);
        }
    }

    // Actualizar rutas de resultados con rutas relativas
    private function updateResultPaths(string $taskId, array $savedResults): void
    {
        try {
            $record = VirtualTryOn::where('task_id', $taskId)->first();

            if ($record && !empty($savedResults)) {
                $relativePaths = [];
                foreach ($savedResults as $result) {
                    $relativePaths[] = $result['saved_path'];
                }

                $record->update([
                    'result_image_paths' => $relativePaths,
                    'status' => 'completed',
                ]);

                Log::info('VirtualTryOn result paths updated', [
                    'user' => 'StivenCard',
                    'task_id' => $taskId,
                    'paths_count' => count($relativePaths),
                    'paths' => $relativePaths,
                    'timestamp' => now(),
                ]);
            }

        } catch (\Exception $e) {
            Log::warning('Failed to update VirtualTryOn result paths', [
                'user' => 'StivenCard',
                'task_id' => $taskId,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function createVirtualTryOnRecord(Request $request, array $response, array $storedPaths): void
    {
        try {
            VirtualTryOn::create([
                'task_id' => $response['data']['task_id'],
                'model_name' => 'kolors-virtual-try-on-v1-5',
                'model_type' => $request->input('model_source'),
                'human_image_path' => $this->getHumanImagePath($request, $storedPaths),
                'garments_type' => $request->input('garment_type'),
                'cloth_image_path' => $this->getClothImagePath($request, $storedPaths),
                'output_count' => $request->input('output_count'),
                'result_image_paths' => null,
                'status' => $this->mapKlingStatus($response['data']['task_status'] ?? 'submitted'),
            ]);

            Log::info('VirtualTryOn record created', [
                'user' => 'StivenCard',
                'task_id' => $response['data']['task_id'],
                'model_type' => $request->input('model_source'),
                'garment_type' => $request->input('garment_type'),
                'timestamp' => now(),
            ]);

        } catch (\Exception $e) {
            Log::warning('Failed to create VirtualTryOn record', [
                'user' => 'StivenCard',
                'error' => $e->getMessage(),
                'task_id' => $response['data']['task_id'] ?? 'unknown'
            ]);
        }
    }

    private function createApiLogRecord(Request $request, ?array $response, array $payload, ?\Exception $error = null): void
    {
        try {
            $requestData = [
                'model_source' => $request->input('model_source'),
                'selected_default_model' => $request->input('selected_default_model'),
                'garment_type' => $request->input('garment_type'),
                'output_count' => $request->input('output_count'),
                'has_human_image' => $request->hasFile('human_image'),
                'has_single_garment' => $request->hasFile('single_garment'),
                'has_top_garment' => $request->hasFile('top_garment'),
                'has_bottom_garment' => $request->hasFile('bottom_garment'),
                'payload_structure' => array_keys($payload),
                'human_image_size' => isset($payload['human_image']) ? strlen($payload['human_image']) . ' bytes' : null,
                'cloth_image_size' => isset($payload['cloth_image']) ? strlen($payload['cloth_image']) . ' bytes' : null,
            ];

            ApiLog::create([
                'operation_type' => 'virtual_try_on',
                'task_id' => $response['data']['task_id'] ?? null,
                'model_name' => 'kolors-virtual-try-on-v1-5',
                'prompt' => null,
                'hints_used' => null,
                'status' => $error ? 'failed' : $this->mapKlingStatus($response['data']['task_status'] ?? 'submitted'),
                'task_status' => $response['data']['task_status'] ?? null,
                'task_status_msg' => $response['data']['task_status_msg'] ?? null,
                'request_data' => $requestData,
                'response_data' => $response,
                'error_details' => $error ? [
                    'message' => $error->getMessage(),
                    'file' => $error->getFile(),
                    'line' => $error->getLine(),
                ] : null,
                'endpoint' => '/v1/images/kolors-virtual-try-on',
                'http_method' => 'POST',
            ]);

            Log::info('ApiLog POST record created', [
                'user' => 'StivenCard',
                'operation' => 'virtual_try_on',
                'task_id' => $response['data']['task_id'] ?? null,
                'has_error' => $error !== null,
                'timestamp' => now(),
            ]);

        } catch (\Exception $e) {
            Log::warning('Failed to create ApiLog POST record', [
                'user' => 'StivenCard',
                'error' => $e->getMessage(),
                'original_task_id' => $response['data']['task_id'] ?? 'unknown'
            ]);
        }
    }

    private function updateVirtualTryOnRecord(string $taskId, array $response): void
    {
        try {
            $record = VirtualTryOn::where('task_id', $taskId)->first();

            if ($record && isset($response['data'])) {
                $record->update([
                    'status' => $this->mapKlingStatus($response['data']['task_status'] ?? 'processing'),
                ]);

                Log::info('VirtualTryOn record updated', [
                    'user' => 'StivenCard',
                    'task_id' => $taskId,
                    'new_status' => $record->status,
                    'timestamp' => now(),
                ]);
            }

        } catch (\Exception $e) {
            Log::warning('Failed to update VirtualTryOn record', [
                'user' => 'StivenCard',
                'task_id' => $taskId,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function getHumanImagePath(Request $request, array $storedPaths): ?string
    {
        if ($request->input('model_source') === 'upload') {
            return $storedPaths['human_image'] ?? null;
        }

        if ($request->input('model_source') === 'default') {
            return 'default_models/' . $request->input('selected_default_model');
        }

        return null;
    }

    private function getClothImagePath(Request $request, array $storedPaths): ?string
    {
        if ($request->input('garment_type') === 'single') {
            return $storedPaths['garment_image'] ?? null;
        }

        if ($request->input('garment_type') === 'multiple') {
            return $storedPaths['combined_garment'] ?? null;
        }

        return null;
    }

    private function mapKlingStatus(string $klingStatus): string
    {
        return match($klingStatus) {
            'submitted' => 'submitted',
            'processing' => 'processing',
            'succeed' => 'completed', // 🔥 IMPORTANTE: succeed -> completed
            'failed' => 'failed',
            default => 'processing'
        };
    }

    // MÉTODOS EXISTENTES SIN CAMBIOS
    private function saveInputImages(Request $request): array
    {
        $savedPaths = [];

        try {
            if ($request->model_source === 'upload' && $request->hasFile('human_image')) {
                $savedPaths['human_image'] = $this->imageService->saveImage(
                    $request->file('human_image'),
                    'klingai/tryon_inputs/human',
                    'human_'
                );
            } elseif ($request->model_source === 'default') {
                $savedPaths['selected_default_model'] = $request->input('selected_default_model');
            }

            if ($request->garment_type === 'single' && $request->hasFile('single_garment')) {
                $savedPaths['garment_image'] = $this->imageService->saveImage(
                    $request->file('single_garment'),
                    'klingai/tryon_inputs/garments',
                    'single_'
                );
            } elseif ($request->garment_type === 'multiple') {
                if ($request->hasFile('top_garment') && $request->hasFile('bottom_garment')) {
                    $savedPaths['combined_garment'] = $this->imageService->saveCombinedImage(
                        $request->file('top_garment'),
                        $request->file('bottom_garment'),
                        'klingai/combined_garments'
                    );
                }
            }

            return $savedPaths;
        } catch (\Exception $e) {
            Log::warning('Error saving input images', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function saveResultImages(array $images): array
    {
        $savedResults = [];

        foreach ($images as $image) {
            try {
                $savedPath = $this->imageService->downloadAndSaveImage(
                    $image['url'],
                    'klingai/tryon_results',
                    'result_'
                );

                $savedResults[] = [
                    'index' => $image['index'],
                    'original_url' => $image['url'],
                    'saved_path' => $savedPath,
                    'local_url' => Storage::url($savedPath)
                ];
            } catch (\Exception $e) {
                Log::warning('Error saving result image', [
                    'url' => $image['url'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $savedResults;
    }

    private function processHumanImage(Request $request)
    {
        if ($request->model_source === 'upload') {
            return $this->imageService->convertToBase64($request->file('human_image'));
        }
        if ($request->model_source === 'virtual') {
            return 'base64-string-of-virtual-model';
        }
        return $this->imageService->getSelectedDefaultModelBase64($request->input('selected_default_model'));
    }

    private function processGarments(Request $request)
    {
        if ($request->garment_type === 'single') {
            return $this->imageService->convertToBase64($request->file('single_garment'));
        }
        return $this->imageService->combineImagesToBase64(
            $request->file('top_garment'),
            $request->file('bottom_garment')
        );
    }

    public function getDefaultModels()
    {
        $modelsPath = public_path('klingai/default_models');
        $defaultModels = [];

        if (is_dir($modelsPath)) {
            $files = scandir($modelsPath);
            foreach ($files as $file) {
                if (in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png'])) {
                    $defaultModels[] = [
                        'filename' => $file,
                        'url' => asset('klingai/default_models/' . $file),
                        'name' => pathinfo($file, PATHINFO_FILENAME)
                    ];
                }
            }
        }

        return $defaultModels;
    }
}
