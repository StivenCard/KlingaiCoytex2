<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\KlingAi\KlingApiService;
use App\Services\KlingAi\ImageProcessingService;
use App\Services\GuidelinesService;
use App\Models\VirtualTryOn;
use Illuminate\Support\Facades\Storage;

class VirtualTryOnController extends Controller
{
    public function __construct(
        private KlingApiService $api,
        private ImageProcessingService $images,
        private GuidelinesService $guidelines
    ) {}

    public function show()
    {
        $defaultModels = $this->guidelines->getDefaultModels();
        $existingTryOns = VirtualTryOn::latest()->take(20)->get();
        $guidelines = $this->guidelines->getAllGuidelines();

        // Extraer las guidelines individuales
        $validModels = $guidelines['validModels'];
        $invalidModels = $guidelines['invalidModels'];
        $validGarments = $guidelines['validGarments'];
        $invalidGarments = $guidelines['invalidGarments'];

        return view('virtual-try-on', compact(
            'defaultModels',
            'existingTryOns',
            'validModels',
            'invalidModels',
            'validGarments',
            'invalidGarments'
        ));
    }

    public function generate(Request $request)
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

        try {
            $response = $this->api->createVirtualTryOn([
                'model_name' => 'kolors-virtual-try-on-v1-5',
                'human_image' => $this->getHumanImage($request),
                'cloth_image' => $this->getGarmentImage($request),
            ]);

            if ($taskId = $response['data']['task_id'] ?? null) {
                VirtualTryOn::create([
                    'task_id' => $taskId,
                    'model_name' => 'kolors-virtual-try-on-v1-5',
                    'model_type' => $request->model_source,
                    'human_image_path' => $this->getHumanPath($request),
                    'garments_type' => $request->garment_type,
                    'cloth_image_path' => $this->getGarmentPath($request),
                    'output_count' => $request->output_count,
                    'status' => 'processing',
                ]);
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function taskStatus(string $taskId)
    {
        try {
            $response = $this->api->getVirtualTryOnResult($taskId);
            $tryOn = VirtualTryOn::where('task_id', $taskId)->first();

            if (!$tryOn) {
                return response()->json(['error' => 'Task not found'], 404);
            }

            $status = $response['data']['task_status'];
            $tryOn->update(['status' => $status === 'succeed' ? 'completed' : 'processing']);

            if ($status === 'succeed') {
                $results = [];
                foreach ($response['data']['task_result']['images'] ?? [] as $image) {
                    try {
                        $results[] = $this->images->downloadAndSaveImage(
                            $image['url'], 'klingai/tryon_results', 'result_'
                        );
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                if ($results) {
                    $tryOn->update(['result_image_paths' => $results, 'status' => 'completed']);
                    $response['data']['local_images'] = array_map(fn($p) => Storage::url($p), $results);
                }
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    private function getHumanImage($request): string
    {
        return match($request->model_source) {
            'upload' => $this->images->convertToBase64($request->file('human_image')),
            'virtual' => 'base64-string-of-virtual-model',
            'default' => $this->images->getSelectedDefaultModelBase64($request->selected_default_model),
            default => throw new \Exception('Invalid model source')
        };
    }

    private function getGarmentImage($request): string
    {
        return match($request->garment_type) {
            'single' => $this->images->convertToBase64($request->file('single_garment')),
            'multiple' => $this->images->combineImagesToBase64(
                $request->file('top_garment'), $request->file('bottom_garment')
            ),
            default => throw new \Exception('Invalid garment type')
        };
    }

    private function getHumanPath($request): string|null
    {
        if ($request->model_source === 'upload' && $request->hasFile('human_image')) {
            return $this->images->saveImage($request->file('human_image'), 'klingai/tryon_inputs/human', 'human_');
        }
        if ($request->model_source === 'default') {
            return 'default_models/' . $request->selected_default_model;
        }
        return null;
    }

    private function getGarmentPath($request): string|null
    {
        if ($request->garment_type === 'single' && $request->hasFile('single_garment')) {
            return $this->images->saveImage($request->file('single_garment'), 'klingai/tryon_inputs/garments', 'single_');
        }
        if ($request->garment_type === 'multiple' && $request->hasFile('top_garment') && $request->hasFile('bottom_garment')) {
            return $this->images->saveCombinedImage($request->file('top_garment'), $request->file('bottom_garment'), 'klingai/combined_garments');
        }
        return null;
    }
}
