<?php

namespace App\Http\Controllers;

use App\Models\OmniModel;
use App\Models\PricingRule;
use App\Models\VirtualModel;
use App\Services\GuidelinesService;
use App\Services\KlingAi\ImageProcessingService;
use App\Services\KlingAi\KlingApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OmniModelController extends Controller
{
    public function __construct(
        private KlingApiService $api,
        private ImageProcessingService $images,
        private GuidelinesService $guidelines
    ) {}

    /**
     * Muestra la vista principal del módulo Omni Try-On.
     */
    public function show()
    {
        $defaultModels = $this->guidelines->getDefaultModels();

        $virtualModels = VirtualModel::where('status', 'completed')
            ->latest()
            ->take(20)
            ->get();

        $existingOmni = OmniModel::where('status', '!=', 'failed')
            ->latest()
            ->take(20)
            ->get();

        $guidelines = $this->guidelines->getAllGuidelines();

        return view('omni-try-on', [
            'defaultModels'  => $defaultModels,
            'virtualModels'  => $virtualModels,
            'existingTryOns' => $existingOmni,
            'validModels'    => $guidelines['validModels'],
            'invalidModels'  => $guidelines['invalidModels'],
            'validGarments'  => $guidelines['validGarments'],
            'invalidGarments'=> $guidelines['invalidGarments'],
        ]);
    }

    /**
     * Envía la solicitud de Try-On usando la API Omni.
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'model_source'           => 'required|in:virtual,default,upload',
            'selected_default_model' => 'required_if:model_source,default',
            'selected_virtual_model' => 'required_if:model_source,virtual|exists:virtual_models,id',
            'selected_virtual_index' => 'nullable|integer|min:0|max:3',
            'human_image'            => 'required_if:model_source,upload|file|image|mimes:jpg,jpeg,png|max:51200',
            'garment_type'           => 'required|in:single,multiple',
            'single_garment'         => 'required_if:garment_type,single|file|image|mimes:jpg,jpeg,png|max:51200',
            'top_garment'            => 'required_if:garment_type,multiple|file|image|mimes:jpg,jpeg,png|max:51200',
            'bottom_garment'         => 'required_if:garment_type,multiple|file|image|mimes:jpg,jpeg,png|max:51200',
            'output_count'           => 'required|integer|min:1|max:4',
            'aspect_ratio'           => 'nullable|in:16:9,9:16,1:1,4:3,3:4,3:2,2:3,21:9,auto',
            'resolution'             => 'nullable|in:1k,2k,4k',
        ]);

        try {
            // 1. Guardar imágenes locales para trazabilidad
            $humanImagePath = $this->getHumanPath($request);
            $garmentImagePath = $this->getGarmentPath($request);

            // 2. Definir Prompt y parámetros Omni
            $prompt = $request->input('prompt', 'Fit the outfit from <<<image_2>>> onto the model in <<<image_1>>> accurately while maintaining identity and realistic proportions.');
            $aspectRatio = $request->input('aspect_ratio', 'auto');
            $resolution = $request->input('resolution', '1k');

            // 3. Estructurar Payload para API Omni
            $payload = [
                'model_name'   => 'kling-v3-omni',
                'prompt'       => $prompt,
                'image_list'   => [
                    ['image' => $this->getHumanImage($request)],
                    ['image' => $this->getGarmentImage($request)],
                ],
                'resolution'   => $resolution,
                'aspect_ratio' => $aspectRatio,
                'n'            => (int) $request->output_count,
            ];

            // 4. Consumo de API
            $response = $this->api->createOmniImage($payload, $humanImagePath, $garmentImagePath);

            if ($taskId = $response['data']['task_id'] ?? null) {
                $pricing = PricingRule::where('model_name', 'kling-v3-omni')
                    ->where('mode', 'default')
                    ->first();

                OmniModel::create([
                    'task_id'          => $taskId,
                    'user_id'          => Auth::id() ?? '321123',
                    'model_name'       => 'kling-v3-omni',
                    'model_type'       => $request->model_source,
                    'prompt'           => $prompt,
                    'human_image_path' => $humanImagePath,
                    'garments_type'    => $request->garment_type,
                    'cloth_image_path' => $garmentImagePath,
                    'output_count'     => $request->output_count,
                    'aspect_ratio'     => $aspectRatio,
                    'resolution'       => $resolution,
                    'tokens'           => $pricing->tokens ?? null,
                    'price'            => $pricing->price ?? null,
                    'status'           => 'processing',
                ]);
            }

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Consulta el estado de la tarea Omni y procesa los resultados al finalizar.
     */
    public function taskStatus(string $taskId): JsonResponse
    {
        try {
            $response = $this->api->getOmniImageResult($taskId);
            $omni = OmniModel::where('task_id', $taskId)->first();

            if (!$omni) {
                return response()->json(['error' => 'Task not found'], 404);
            }

            $status = $response['data']['task_status'];

            $omni->update([
                'status' => match($status) {
                    'succeed' => 'completed',
                    'failed'  => 'failed',
                    default   => 'processing',
                }
            ]);

            if ($status === 'succeed') {
                $results = [];

                foreach ($response['data']['task_result']['images'] ?? [] as $image) {
                    try {
                        $results[] = $this->images->downloadAndSaveImage(
                            $image['url'],
                            'klingai/omni_results',
                            'omni_result_'
                        );
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                if ($results) {
                    $omni->update([
                        'result_image_paths' => $results,
                        'status'             => 'completed',
                    ]);

                    $response['data']['local_images'] = array_map(
                        fn($p) => Storage::url($p),
                        $results
                    );
                }
            }

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    // Auxiliares idénticos a la lógica previa
    private function getHumanImage(Request $request): string
    {
        return match($request->model_source) {
            'upload'  => $this->images->convertToBase64($request->file('human_image')),
            'virtual' => $this->images->getVirtualModelBase64(
                $request->selected_virtual_model,
                (int) $request->input('selected_virtual_index', 0)
            ),
            'default' => $this->images->getSelectedDefaultModelBase64($request->selected_default_model),
            default   => throw new \Exception('Invalid model source')
        };
    }

    private function getGarmentImage(Request $request): string
    {
        return match($request->garment_type) {
            'single'   => $this->images->convertToBase64($request->file('single_garment')),
            'multiple' => $this->images->combineImagesToBase64(
                $request->file('top_garment'),
                $request->file('bottom_garment')
            ),
            default    => throw new \Exception('Invalid garment type')
        };
    }

    private function getHumanPath(Request $request): ?string
    {
        if ($request->model_source === 'upload' && $request->hasFile('human_image')) {
            return $this->images->saveImage($request->file('human_image'), 'klingai/omni_inputs/human', 'human_');
        }

        if ($request->model_source === 'default') {
            return 'klingai/default_models/' . $request->selected_default_model;
        }

        if ($request->model_source === 'virtual') {
            $model = VirtualModel::find($request->selected_virtual_model);
            $index = (int) $request->input('selected_virtual_index', 0);
            return $model && isset($model->result_image_paths[$index])
                ? $model->result_image_paths[$index]
                : null;
        }

        return null;
    }

    private function getGarmentPath(Request $request): ?string
    {
        if ($request->garment_type === 'single' && $request->hasFile('single_garment')) {
            return $this->images->saveImage($request->file('single_garment'), 'klingai/omni_inputs/garments', 'single_');
        }

        if ($request->garment_type === 'multiple' && $request->hasFile('top_garment') && $request->hasFile('bottom_garment')) {
            return $this->images->saveCombinedImage(
                $request->file('top_garment'),
                $request->file('bottom_garment'),
                'klingai/omni_combined_garments'
            );
        }

        return null;
    }
}
