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
use Illuminate\Support\Facades\Log;
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
            'defaultModels'   => $defaultModels,
            'virtualModels'   => $virtualModels,
            'existingTryOns'  => $existingOmni,
            'validModels'     => $guidelines['validModels'],
            'invalidModels'   => $guidelines['invalidModels'],
            'validGarments'   => $guidelines['validGarments'],
            'invalidGarments' => $guidelines['invalidGarments'],
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
            $prompt = $this->getOmniPrompt($request);
            $aspectRatio = $request->input('aspect_ratio', 'auto');
            $resolution = $request->input('resolution', '1k');

            // 3. Estructurar Payload para API Omni
            $payload = [
                'model_name'   => 'kling-v3-omni',
                'prompt'       => $prompt,
                'image_list'   => $this->getOmniImageList($request),
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
        } catch (\Throwable $e) {
            Log::error('Omni generate: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Consulta el estado de la tarea Omni y procesa los resultados al finalizar.
     *
     * - Solo marca "completed" si realmente se guardó al menos una imagen.
     * - Si Kling o la descarga fallan de forma pasajera, la fila NO se marca como fallida:
     *   queda en "processing" y se reintenta en la siguiente consulta.
     */
    public function taskStatus(string $taskId): JsonResponse
    {
        $omni = OmniModel::where('task_id', $taskId)->first();

        if (!$omni) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        // Ya guardado: no volvemos a consultar a Kling ni a descargar
        if ($omni->status === 'completed' && !empty($omni->result_image_paths)) {
            return response()->json([
                'data' => [
                    'task_status'  => 'succeed',
                    'local_images' => array_map(fn($p) => Storage::url($p), $omni->result_image_paths),
                ],
            ]);
        }

        try {
            $response = $this->api->getOmniImageResult($taskId);
        } catch (\Throwable $e) {
            // Falla pasajera al consultar a Kling: no se toca la fila, el front reintenta
            Log::warning("Omni {$taskId}: no se pudo consultar a Kling - " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 502);
        }

        $status = $response['data']['task_status'] ?? 'processing';

        if ($status === 'failed') {
            $omni->update(['status' => 'failed']);
            return response()->json($response);
        }

        if ($status !== 'succeed') {
            return response()->json($response); // submitted / processing
        }

        // Omni puede devolver las imágenes en "images" o en "series_images"
        $result = $response['data']['task_result'] ?? [];
        $items = !empty($result['images']) ? $result['images'] : ($result['series_images'] ?? []);

        $paths = [];
        $lastError = null;

        foreach ($items as $image) {
            if (empty($image['url'])) {
                continue;
            }

            try {
                $paths[] = $this->images->downloadAndSaveImage(
                    $image['url'],
                    'klingai/omni_results',
                    'omni_result_'
                );
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                Log::error("Omni {$taskId}: no se pudo guardar una imagen - " . $lastError);
            }
        }

        if (!$paths) {
            Log::error("Omni {$taskId}: Kling reportó succeed pero no se guardó ninguna imagen", [
                'task_result' => $result,
            ]);

            // La fila queda en "processing" para reintentar en la próxima consulta
            $response['data']['task_status'] = 'failed';
            $response['data']['task_status_msg'] = $lastError
                ? 'No se pudo guardar la imagen: ' . $lastError
                : 'Kling no devolvió imágenes para esta tarea';

            return response()->json($response);
        }

        $omni->update(['result_image_paths' => $paths, 'status' => 'completed']);
        $response['data']['local_images'] = array_map(fn($p) => Storage::url($p), $paths);

        return response()->json($response);
    }

    // ---------------------------------------------------------------
    // Auxiliares
    // ---------------------------------------------------------------

    private function getHumanImage(Request $request): string
    {
        return match ($request->model_source) {
            'upload'  => $this->images->convertToBase64($request->file('human_image')),
            'virtual' => $this->images->getVirtualModelBase64(
                $request->selected_virtual_model,
                (int) $request->input('selected_virtual_index', 0)
            ),
            'default' => $this->images->getSelectedDefaultModelBase64($request->selected_default_model),
            default   => throw new \Exception('Invalid model source')
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

    /**
     * image_1 = modelo humano, image_2 (y image_3) = prenda(s), enviadas por separado.
     */
    private function getOmniImageList(Request $request): array
    {
        $images = [
            ['image' => $this->getHumanImage($request)],
        ];

        if ($request->garment_type === 'single') {
            $images[] = ['image' => $this->images->convertToBase64($request->file('single_garment'))];
        }

        if ($request->garment_type === 'multiple') {
            $images[] = ['image' => $this->images->convertToBase64($request->file('top_garment'))];
            $images[] = ['image' => $this->images->convertToBase64($request->file('bottom_garment'))];
        }

        return $images;
    }

    private function getOmniPrompt(Request $request): string
    {
        if ($request->filled('prompt')) {
            return $request->input('prompt');
        }

        if ($request->garment_type === 'single') {
            return 'Dress the person in <<<image_1>>> with the clothing item shown in <<<image_2>>>. Analyze the clothing item visually and determine its actual garment type and the appropriate area of the body where it should naturally be worn. Do not assume its placement based on image order, input field name, or any predefined garment category. The clothing item may be any type of garment, including upper-body, lower-body, full-body or other wearable clothing. Place it naturally and realistically according to its actual design and function. Preserve the person\'s identity, face, body proportions, pose and background. Preserve the garment\'s original color, texture, pattern, material, shape and design details. Make the garment fit naturally and realistically.';
        }

        return 'Dress the person in <<<image_1>>> using BOTH clothing items shown in <<<image_2>>> and <<<image_3>>>. Analyze each clothing item visually and determine its actual garment type, structure, design and the appropriate area of the body where it should naturally be worn. Do NOT assume that image_2 or image_3 represents a specific type of garment. Do NOT assign garment roles based on image order, input field name, or predefined categories. The garments may be any combination of clothing items, including but not limited to shirts, blouses, sweaters, jackets, pants, jeans, shorts, skirts, dresses or other garments. Determine independently where each garment belongs on the person\'s body and place it accordingly. Use BOTH garments together as one complete outfit, regardless of the order in which they are provided. Preserve the person\'s identity, face, body proportions, pose and background. Preserve the original color, texture, pattern, material, shape and design details of each garment. Make all garments fit naturally and realistically without changing their design.';
    }
}
