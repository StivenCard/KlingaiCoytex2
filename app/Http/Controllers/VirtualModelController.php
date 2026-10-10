<?php

namespace App\Http\Controllers;

use App\Models\PricingRule;
use App\Models\VirtualModel;
use App\Services\HintsService;
use App\Services\KlingAi\ImageProcessingService;
use App\Services\KlingAi\KlingApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VirtualModelController extends Controller
{
    private const MODEL_NAME = 'kling-v3';
    private const OUTPUT_COUNT = 1;

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

    /**
     * Crea la tarea en Kling (POST /v1/images/generations) y registra el modelo en "processing".
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'gender'       => 'required|in:male,female',
            'age_group'    => 'required|in:children,youth,elderly',
            'skin_tone'    => 'required|in:light,medium,dark,olive',
            'aspect_ratio' => 'required|in:1:1,9:16,2:3,3:4',
            'prompt'       => 'nullable|string|max:2500',
        ]);

        try {
            $prompt = $this->buildPrompt($request);

            // Solo parámetros que existen en la doc de Kling.
            // Género/edad/tono van dentro del prompt y se guardan en la BD.
            $response = $this->api->createImageGenerationTask([
                'model_name'   => self::MODEL_NAME,
                'prompt'       => $prompt,
                'aspect_ratio' => $request->aspect_ratio,
                'n'            => self::OUTPUT_COUNT,
            ]);

            if ($taskId = $response['data']['task_id'] ?? null) {
                $pricing = PricingRule::where('model_name', 'kling-v3-omni')
                    ->where('mode', 'text-to-image')
                    ->first();

                VirtualModel::create([
                    'task_id'      => $taskId,
                    'user_id'      => Auth::id() ?? '123321',
                    'model_name'   => self::MODEL_NAME,
                    'prompt'       => $prompt,
                    'gender'       => $request->gender,
                    'age_group'    => $request->age_group,
                    'skin_tone'    => $request->skin_tone,
                    'aspect_ratio' => $request->aspect_ratio,
                    'output_count' => self::OUTPUT_COUNT,
                    'tokens'       => $pricing?->tokens ?? 0,
                    'price'        => $pricing?->price ?? 0,
                    'status'       => 'processing',
                ]);
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Consulta la tarea en Kling. Si terminó, descarga las imágenes y actualiza el modelo.
     */
    public function taskStatus(string $taskId): JsonResponse
    {
        $model = VirtualModel::where('task_id', $taskId)->first();

        if (!$model) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        // Ya procesado: no consultamos Kling ni descargamos de nuevo
        if ($model->status === 'completed' && !empty($model->result_image_paths)) {
            return response()->json([
                'data' => [
                    'task_status'  => 'succeed',
                    'local_images' => array_map(fn($p) => Storage::url($p), $model->result_image_paths),
                ],
            ]);
        }

        try {
            $response = $this->api->getImageGenerationResult($taskId);
            $status = $response['data']['task_status'] ?? 'processing';

            if ($status === 'failed') {
                $model->update(['status' => 'failed']);
            }

            if ($status === 'succeed') {
                $paths = [];

                foreach ($response['data']['task_result']['images'] ?? [] as $image) {
                    try {
                        $paths[] = $this->images->downloadAndSaveImage(
                            $image['url'],
                            'klingai/virtual_models',
                            'model_'
                        );
                    } catch (\Exception $e) {
                        Log::warning("VirtualModel {$taskId}: no se pudo descargar una imagen - " . $e->getMessage());
                    }
                }

                if ($paths) {
                    $model->update(['result_image_paths' => $paths, 'status' => 'completed']);
                    $response['data']['local_images'] = array_map(fn($p) => Storage::url($p), $paths);
                } else {
                    // Kling terminó pero no pudimos guardar nada: lo reflejamos como fallido
                    $model->update(['status' => 'failed']);
                    $response['data']['task_status'] = 'failed';
                }
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Prompt base (siempre con género, edad y tono) + texto/hint del usuario si existe.
     */
    private function buildPrompt(Request $request): string
    {
        $subject = $this->describeSubject($request->gender, $request->age_group);
        $skin = $this->mapSkinTone($request->skin_tone);

        $base = "Full-body fashion model photo of {$subject}, {$skin} skin tone, Colombian features, "
            . 'natural proportions, realistic, no distortions, sharp focus, high quality';

        if ($request->filled('prompt')) {
            // El usuario/hint define pose, ropa y fondo, así que no forzamos el fondo
            return Str::limit("{$base}. " . trim($request->prompt), 2500, '');
        }

        return "{$base}, simple plain studio background.";
    }

    private function describeSubject(string $gender, string $age): string
    {
        $female = $gender === 'female';

        return match ($age) {
            'children' => $female ? 'a young girl' : 'a young boy',
            'elderly'  => $female ? 'an elderly woman' : 'an elderly man',
            default    => $female ? 'a young adult woman' : 'a young adult man',
        };
    }

    private function mapSkinTone(string $tone): string
    {
        return match ($tone) {
            'light'  => 'light',
            'medium' => 'medium',
            'dark'   => 'dark',
            'olive'  => 'olive',
            default  => 'medium',
        };
    }
}
