<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\KlingAi\KlingApiService;
use App\Services\KlingAi\ImageProcessingService;
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
        // Obtener modelos default disponibles
        $defaultModels = $this->getDefaultModels();

        return view('virtual-try-on', compact('defaultModels'));
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
            // Guardar imágenes localmente
            $storedPaths = $this->saveInputImages($request);

            // Procesar imagen del modelo humano
            $humanImage = $this->processHumanImage($request);

            // Procesar imagen de prenda
            $clothImage = $this->processGarments($request);

            // Payload para la API
            $payload = [
                'model_name' => 'kolors-virtual-try-on-v1-5',
                'human_image' => $humanImage,
                'cloth_image' => $clothImage,
                'n' => (int) $request->input('output_count', 1),
            ];

            // Llamada a la API
            $response = $this->apiService->virtualTryOn($payload);

            // Log simple
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
            $response = $this->apiService->checkTaskStatus($taskId);

            // Descargar y guardar resultados si están listos
            if (isset($response['data']['task_status']) && $response['data']['task_status'] === 'succeed') {
                $savedResults = $this->saveResultImages($response['data']['task_result']['images'] ?? []);

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
            Log::error('Virtual Try-On Status Error', [
                'task_id' => $taskId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    // Guardar imágenes de entrada
    private function saveInputImages(Request $request): array
    {
        $savedPaths = [];

        try {
            // Guardar imagen del modelo humano
            if ($request->model_source === 'upload' && $request->hasFile('human_image')) {
                $savedPaths['human_image'] = $this->imageService->saveImage(
                    $request->file('human_image'),
                    'klingai/tryon_inputs/human',
                    'human_'
                );
            } elseif ($request->model_source === 'default') {
                // Registrar qué modelo default se usó
                $savedPaths['selected_default_model'] = $request->input('selected_default_model');
            }

            // Guardar imagen(es) de prenda
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

    // Guardar resultados de la API
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
            // TODO: Obtener base64 de modelo virtual generado
            return 'base64-string-of-virtual-model';
        }
        // Default model seleccionado
        return $this->imageService->getSelectedDefaultModelBase64($request->input('selected_default_model'));
    }

    private function processGarments(Request $request)
    {
        if ($request->garment_type === 'single') {
            return $this->imageService->convertToBase64($request->file('single_garment'));
        }
        // Multiple garments: combinar horizontalmente
        return $this->imageService->combineImagesToBase64(
            $request->file('top_garment'),
            $request->file('bottom_garment')
        );
    }
}
