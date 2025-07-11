<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\KlingAi\KlingApiService;
use App\Services\KlingAi\ImageProcessingService;
use App\Services\GuidelinesService;
use App\Models\VirtualTryOn;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class VirtualTryOnController extends Controller
{
    public function __construct(
        private KlingApiService $apiService,
        private ImageProcessingService $imageService,
        private GuidelinesService $guidelinesService
    ) {}

    /**
     * Mostrar la vista principal de Virtual Try-On
     */
    public function show()
    {
        $defaultModels = $this->guidelinesService->getDefaultModels();

        $existingTryOns = VirtualTryOn::latest()->take(20)->get();

        $guidelines = $this->guidelinesService->getAllGuidelines();

        return view('virtual-try-on', array_merge(
            compact('defaultModels', 'existingTryOns'),
            $guidelines
        ));
    }

    /**
     * Generar un nuevo Virtual Try-On
     */
    public function generate(Request $request): JsonResponse
    {
        $this->validateRequest($request);

        try {
            $storedPaths = $this->saveInputImages($request);
            $payload = $this->buildApiPayload($request);

            $response = $this->apiService->createVirtualTryOn($payload);

            if (isset($response['data']['task_id'])) {
                $this->createVirtualTryOnRecord($request, $response['data']['task_id'], $storedPaths);
            }

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Verificar el estado de una tarea
     */
    public function taskStatus(string $taskId): JsonResponse
    {
        try {
            $response = $this->apiService->getVirtualTryOnResult($taskId);
            $tryOn = VirtualTryOn::where('task_id', $taskId)->first();

            if (!$tryOn) {
                return response()->json(['error' => 'Task not found'], 404);
            }

            $this->updateTryOnStatus($tryOn, $response);

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Validar la request
     */
    private function validateRequest(Request $request): void
    {
        $request->validate([
            'model_source' => 'required|in:virtual,default,upload',
            'selected_default_model' => 'required_if:model_source,default',
            'human_image' => 'required_if:model_source,upload|file|image|max:51200',
            'garment_type' => 'required|in:single,multiple',
            'single_garment' => 'required_if:garment_type,single|file|image|max:51200',
            'top_garment' => 'required_if:garment_type,multiple|file|image|max:51200',
            'bottom_garment' => 'required_if:garment_type,multiple|file|image|max:51200',
            'output_count' => 'required|integer|min:1|max:4',
        ]);
    }

    /**
     * Construir payload para la API
     */
    private function buildApiPayload(Request $request): array
    {
        return [
            'model_name' => 'kolors-virtual-try-on-v1-5',
            'human_image' => $this->processHumanImage($request),
            'cloth_image' => $this->processGarmentImage($request),
        ];
    }

    /**
     * Crear registro en base de datos
     */
    private function createVirtualTryOnRecord(Request $request, string $taskId, array $storedPaths): void
    {
        VirtualTryOn::create([
            'task_id' => $taskId,
            'model_name' => 'kolors-virtual-try-on-v1-5',
            'model_type' => $request->model_source,
            'human_image_path' => $this->getHumanImagePath($request, $storedPaths),
            'garments_type' => $request->garment_type,
            'cloth_image_path' => $this->getClothImagePath($request, $storedPaths),
            'output_count' => $request->output_count,
            'status' => 'processing',
        ]);
    }

    /**
     * Actualizar estado del try-on
     */
    private function updateTryOnStatus(VirtualTryOn $tryOn, array &$response): void
    {
        $status = $response['data']['task_status'];

        $tryOn->update([
            'status' => $status === 'succeed' ? 'completed' : 'processing'
        ]);

        if ($status === 'succeed') {
            $this->processTryOnResults($tryOn, $response);
        }
    }

    /**
     * Procesar resultados exitosos
     */
    private function processTryOnResults(VirtualTryOn $tryOn, array &$response): void
    {
        $savedResults = [];

        foreach ($response['data']['task_result']['images'] ?? [] as $image) {
            try {
                $savedPath = $this->imageService->downloadAndSaveImage(
                    $image['url'],
                    'klingai/tryon_results',
                    'result_'
                );
                $savedResults[] = $savedPath;
            } catch (\Exception $e) {
                continue;
            }
        }

        if (!empty($savedResults)) {
            $tryOn->update([
                'result_image_paths' => $savedResults,
                'status' => 'completed'
            ]);

            $response['data']['local_images'] = array_map(
                fn($path) => Storage::url($path),
                $savedResults
            );
        }
    }

    /**
     * Procesar imagen humana según fuente
     */
    private function processHumanImage(Request $request): string
    {
        return match($request->model_source) {
            'upload' => $this->imageService->convertToBase64($request->file('human_image')),
            'virtual' => 'base64-string-of-virtual-model',
            'default' => $this->imageService->getSelectedDefaultModelBase64($request->selected_default_model),
            default => throw new \Exception('Invalid model source')
        };
    }

    /**
     * Procesar imagen de prenda según tipo
     */
    private function processGarmentImage(Request $request): string
    {
        return match($request->garment_type) {
            'single' => $this->imageService->convertToBase64($request->file('single_garment')),
            'multiple' => $this->imageService->combineImagesToBase64(
                $request->file('top_garment'),
                $request->file('bottom_garment')
            ),
            default => throw new \Exception('Invalid garment type')
        };
    }

    /**
     * Guardar imágenes de entrada
     */
    private function saveInputImages(Request $request): array
    {
        $paths = [];

        if ($request->model_source === 'upload' && $request->hasFile('human_image')) {
            $paths['human_image'] = $this->imageService->saveImage(
                $request->file('human_image'),
                'klingai/tryon_inputs/human',
                'human_'
            );
        }

        if ($request->garment_type === 'single' && $request->hasFile('single_garment')) {
            $paths['garment_image'] = $this->imageService->saveImage(
                $request->file('single_garment'),
                'klingai/tryon_inputs/garments',
                'single_'
            );
        } elseif ($request->garment_type === 'multiple' &&
                  $request->hasFile('top_garment') &&
                  $request->hasFile('bottom_garment')) {
            $paths['combined_garment'] = $this->imageService->saveCombinedImage(
                $request->file('top_garment'),
                $request->file('bottom_garment'),
                'klingai/combined_garments'
            );
        }

        return $paths;
    }

    /**
     * Obtener ruta de imagen humana
     */
    private function getHumanImagePath(Request $request, array $storedPaths): ?string
    {
        return match($request->model_source) {
            'upload' => $storedPaths['human_image'] ?? null,
            'default' => 'default_models/' . $request->selected_default_model,
            'virtual' => null,
            default => null
        };
    }

    /**
     * Obtener ruta de imagen de prenda
     */
    private function getClothImagePath(Request $request, array $storedPaths): ?string
    {
        return match($request->garment_type) {
            'single' => $storedPaths['garment_image'] ?? null,
            'multiple' => $storedPaths['combined_garment'] ?? null,
            default => null
        };
    }
}
