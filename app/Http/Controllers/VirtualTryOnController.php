<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\KlingAi\KlingApiService;
use App\Services\KlingAi\ImageProcessingService;
use App\Models\VirtualTryOn;
use Illuminate\Support\Facades\Storage;

class VirtualTryOnController extends Controller
{
    public function __construct(
        private KlingApiService $apiService,
        private ImageProcessingService $imageService
    ) {}

    public function show()
    {
        $defaultModels = $this->getDefaultModels();
        $existingTryOns = VirtualTryOn::latest()->take(20)->get()->map(fn($t) => [
            'id' => $t->id,
            'task_id' => $t->task_id,
            'model_type' => $t->model_type,
            'garments_type' => $t->garments_type,
            'status' => $t->status === 'completed' ? 'completed' : 'processing',
            'created_at' => $t->created_at->toISOString(),
            'result_image_paths' => $t->result_image_paths ?
                array_map(fn($p) => Storage::url($p), $t->result_image_paths) : []
        ]);

        return view('virtual-try-on', compact('defaultModels', 'existingTryOns'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'model_source' => 'required|in:virtual,default,upload',
            'selected_default_model' => 'required_if:model_source,default',
            'human_image' => 'required_if:model_source,upload|file|image|max:51200', // 50MB
            'garment_type' => 'required|in:single,multiple',
            'single_garment' => 'required_if:garment_type,single|file|image|max:51200',
            'top_garment' => 'required_if:garment_type,multiple|file|image|max:51200',
            'bottom_garment' => 'required_if:garment_type,multiple|file|image|max:51200',
            'output_count' => 'required|integer|min:1|max:4',
        ]);

        try {
            $storedPaths = $this->saveInputImages($request);

            $payload = [
                'model_name' => 'kolors-virtual-try-on-v1-5',
                'human_image' => $this->processHumanImage($request),
                'cloth_image' => $this->processGarmentImage($request),
                'n' => (int) $request->output_count,
            ];

            $response = $this->apiService->createVirtualTryOn($payload);

            if (isset($response['data']['task_id'])) {
                VirtualTryOn::create([
                    'task_id' => $response['data']['task_id'],
                    'model_name' => 'kolors-virtual-try-on-v1-5',
                    'model_type' => $request->model_source,
                    'human_image_path' => $this->getHumanImagePath($request, $storedPaths),
                    'garments_type' => $request->garment_type,
                    'cloth_image_path' => $this->getClothImagePath($request, $storedPaths),
                    'output_count' => $request->output_count,
                    'status' => 'processing',
                ]);
            }

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function taskStatus($taskId)
    {
        try {
            $response = $this->apiService->getVirtualTryOnResult($taskId);

            $tryOn = VirtualTryOn::where('task_id', $taskId)->first();
            if (!$tryOn) {
                return response()->json(['error' => 'Task not found'], 404);
            }

            $tryOn->update([
                'status' => $response['data']['task_status'] === 'succeed' ? 'completed' : 'processing'
            ]);

            if ($response['data']['task_status'] === 'succeed') {
                $savedResults = [];
                foreach ($response['data']['task_result']['images'] ?? [] as $image) {
                    try {
                        $savedPath = $this->imageService->downloadAndSaveImage($image['url'], 'klingai/tryon_results', 'result_');
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

                    // 🔥 CRÍTICO: RETORNAR URLs LOCALES
                    $response['data']['local_images'] = array_map(fn($p) => Storage::url($p), $savedResults);
                }
            }

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    private function processHumanImage(Request $request): string
    {
        return match($request->model_source) {
            'upload' => $this->imageService->convertToBase64($request->file('human_image')),
            'virtual' => 'base64-string-of-virtual-model', // TODO: implementar
            'default' => $this->imageService->getSelectedDefaultModelBase64($request->selected_default_model),
            default => throw new \Exception('Invalid model source')
        };
    }

    private function processGarmentImage(Request $request): string
    {
        return match($request->garment_type) {
            'single' => $this->imageService->convertToBase64($request->file('single_garment')),
            'multiple' => $this->imageService->combineImagesToBase64($request->file('top_garment'), $request->file('bottom_garment')),
            default => throw new \Exception('Invalid garment type')
        };
    }

    private function saveInputImages(Request $request): array
    {
        $paths = [];

        if ($request->model_source === 'upload' && $request->hasFile('human_image')) {
            $paths['human_image'] = $this->imageService->saveImage($request->file('human_image'), 'klingai/tryon_inputs/human', 'human_');
        }

        if ($request->garment_type === 'single' && $request->hasFile('single_garment')) {
            $paths['garment_image'] = $this->imageService->saveImage($request->file('single_garment'), 'klingai/tryon_inputs/garments', 'single_');
        } elseif ($request->garment_type === 'multiple' && $request->hasFile('top_garment') && $request->hasFile('bottom_garment')) {
            $paths['combined_garment'] = $this->imageService->saveCombinedImage($request->file('top_garment'), $request->file('bottom_garment'), 'klingai/combined_garments');
        }

        return $paths;
    }

    private function getHumanImagePath(Request $request, array $storedPaths): ?string
    {
        return match($request->model_source) {
            'upload' => $storedPaths['human_image'] ?? null,
            'default' => 'default_models/' . $request->selected_default_model,
            'virtual' => null,
            default => null
        };
    }

    private function getClothImagePath(Request $request, array $storedPaths): ?string
    {
        return match($request->garment_type) {
            'single' => $storedPaths['garment_image'] ?? null,
            'multiple' => $storedPaths['combined_garment'] ?? null,
            default => null
        };
    }

    private function getDefaultModels(): array
    {
        $modelsPath = public_path('klingai/default_models');
        $defaultModels = [];

        if (is_dir($modelsPath)) {
            foreach (scandir($modelsPath) as $file) {
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
