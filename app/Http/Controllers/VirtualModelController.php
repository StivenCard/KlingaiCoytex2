<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\KlingAi\KlingApiService;
use App\Services\KlingAi\ImageProcessingService;
use App\Services\HintsService;
use App\Models\VirtualModel;
use Illuminate\Support\Facades\Storage;

class VirtualModelController extends Controller
{
    public function __construct(
        private KlingApiService $api,
        private ImageProcessingService $images,
        private HintsService $hints
    ) {}

    public function show()
    {
        $hints = $this->hints->getAllHints();
        $existingModels = VirtualModel::latest()->take(20)->get();

        return view('virtual-model', compact('hints', 'existingModels'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'gender' => 'required|in:male,female',
            'age_group' => 'required|in:children,youth,elderly',
            'skin_tone' => 'required|in:light,medium,dark,olive',
            'aspect_ratio' => 'required|in:1:1,9:16,2:3,3:4',
            'output_count' => 'required|integer|min:1|max:4',
            'prompt' => 'nullable|string|max:2500',
        ]);

        try {
            $prompt = $this->buildPrompt($request);

            // 🔥 AGREGAR METADATOS AL REQUEST DATA PARA EL LOG
            $response = $this->api->createImageGenerationTask([
                'model_name' => 'kling-v1-5',
                'prompt' => $prompt,
                'aspect_ratio' => $request->aspect_ratio,
                'n' => $request->output_count,
                // 🔥 METADATOS ADICIONALES PARA EL LOG
                'gender' => $request->gender,
                'age_group' => $request->age_group,
                'skin_tone' => $request->skin_tone,
            ]);

            if ($taskId = $response['data']['task_id'] ?? null) {
                VirtualModel::create([
                    'task_id' => $taskId,
                    'model_name' => 'kling-v1-5',
                    'prompt' => $prompt,
                    'gender' => $request->gender,
                    'age_group' => $request->age_group,
                    'skin_tone' => $request->skin_tone,
                    'aspect_ratio' => $request->aspect_ratio,
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
            $response = $this->api->getImageGenerationResult($taskId);
            $model = VirtualModel::where('task_id', $taskId)->first();

            if (!$model) {
                return response()->json(['error' => 'Task not found'], 404);
            }

            $status = $response['data']['task_status'];
            $model->update(['status' => $status === 'succeed' ? 'completed' : 'processing']);

            if ($status === 'succeed') {
                $results = [];
                foreach ($response['data']['task_result']['images'] ?? [] as $image) {
                    try {
                        $results[] = $this->images->downloadAndSaveImage(
                            $image['url'], 'klingai/virtual_models', 'model_'
                        );
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                if ($results) {
                    $model->update(['result_image_paths' => $results, 'status' => 'completed']);
                    $response['data']['local_images'] = array_map(fn($p) => Storage::url($p), $results);
                }
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    private function buildPrompt(Request $request): string
    {
        if ($request->filled('prompt')) {
            return trim($request->prompt);
        }

        return $this->buildBasePrompt($request);
    }

    private function buildBasePrompt(Request $request): string
    {
        $gender = $request->gender === 'male' ? 'masculino' : 'femenino';
        $age = $this->mapAge($request->age_group);
        $skinTone = $this->mapSkinTone($request->skin_tone);

        return "Crear un modelo de cuerpo completo, con rasgos colombianos, fondo sencillo, sin imperfecciones, sin irregularidades, de genero {$gender}, edad {$age}, tono de piel {$skinTone}.";
    }

    private function mapAge(string $age): string
    {
        return match($age) {
            'children' => 'joven',
            'youth' => 'adulto joven',
            'elderly' => 'adulto mayor',
            default => 'adulto'
        };
    }

    private function mapSkinTone(string $tone): string
    {
        return match($tone) {
            'light' => 'claro',
            'medium' => 'medio',
            'dark' => 'oscuro',
            'olive' => 'oliva',
            default => 'medio'
        };
    }
}
