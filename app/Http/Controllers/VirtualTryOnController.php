<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\KlingAi\KlingApiService;
use App\Services\KlingAi\ImageProcessingService;
use App\Services\GuidelinesService;
use App\Models\VirtualTryOn;
use App\Models\VirtualModel;
use Illuminate\Support\Facades\Storage;

class VirtualTryOnController extends Controller
{
    /**
     * Inyección de dependencias de servicios necesarios para el módulo Try-On.
     *
     * @param KlingApiService $api Servicio para comunicarse con la API de KlingAI
     * @param ImageProcessingService $images Servicio de procesamiento de imágenes (base64, combinación, guardado)
     * @param GuidelinesService $guidelines Servicio para obtener modelos e imágenes de ejemplo válidas/incorrectas
     */
    public function __construct(
        private KlingApiService $api,
        private ImageProcessingService $images,
        private GuidelinesService $guidelines
    ) {}

    /**
     * Muestra la vista principal del módulo Virtual Try-On.
     * Carga:
     * - Modelos por defecto (desde carpeta pública)
     * - Modelos virtuales ya generados por el usuario
     * - Intentos anteriores de Try-On
     * - Imágenes guía válidas e inválidas
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $defaultModels = $this->guidelines->getDefaultModels();

        $virtualModels = VirtualModel::where('status', 'completed')
            ->latest()
            ->take(20)
            ->get();

        // Excluir los Try-Ons fallidos
        $existingTryOns = VirtualTryOn::where('status', '!=', 'failed')
            ->latest()
            ->take(20)
            ->get();

        $guidelines = $this->guidelines->getAllGuidelines();

        return view('virtual-try-on', [
            'defaultModels' => $defaultModels,
            'virtualModels' => $virtualModels,
            'existingTryOns' => $existingTryOns,
            'validModels' => $guidelines['validModels'],
            'invalidModels' => $guidelines['invalidModels'],
            'validGarments' => $guidelines['validGarments'],
            'invalidGarments' => $guidelines['invalidGarments'],
        ]);
    }

    /**
     * Procesa una solicitud para generar una nueva imagen Try-On a través de la API externa de KlingAI.
     * Valida el tipo de modelo, prenda y prepara la imagen en base64.
     * Luego, realiza el llamado y guarda el registro en base de datos con estado `processing`.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(Request $request)
    {
        $request->validate([
            'model_source' => 'required|in:virtual,default,upload',
            'selected_default_model' => 'required_if:model_source,default',
            'selected_virtual_model' => 'required_if:model_source,virtual|exists:virtual_models,id',
            'selected_virtual_index' => 'nullable|integer|min:0|max:3',
            'human_image' => 'required_if:model_source,upload|file|image|max:51200',
            'garment_type' => 'required|in:single,multiple',
            'single_garment' => 'required_if:garment_type,single|file|image|max:51200',
            'top_garment' => 'required_if:garment_type,multiple|file|image|max:51200',
            'bottom_garment' => 'required_if:garment_type,multiple|file|image|max:51200',
            'output_count' => 'required|integer|min:1|max:4',
        ]);

        try {
            // Guardar imágenes para trazabilidad (en disco)
            $humanImagePath = $this->getHumanPath($request);
            $garmentImagePath = $this->getGarmentPath($request);

            // Llamar a la API de Kling con imágenes codificadas
            $response = $this->api->createVirtualTryOn([
                'model_name' => 'kolors-virtual-try-on-v1-5',
                'human_image' => $this->getHumanImage($request),
                'cloth_image' => $this->getGarmentImage($request),
            ], $humanImagePath, $garmentImagePath);


            if ($taskId = $response['data']['task_id'] ?? null) {
                VirtualTryOn::create([
                    'task_id' => $taskId,
                    'model_name' => 'kolors-virtual-try-on-v1-5',
                    'model_type' => $request->model_source,
                    'human_image_path' => $humanImagePath,
                    'garments_type' => $request->garment_type,
                    'cloth_image_path' => $garmentImagePath,
                    'output_count' => $request->output_count,
                    'status' => 'processing',
                ]);
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Consulta el estado de una tarea asíncrona (task_id) en la API de Kling.
     * Si ha finalizado con éxito, descarga las imágenes y actualiza el estado a `completed`.
     *
     * @param string $taskId ID de tarea asignado por Kling
     * @return JsonResponse
     */
    public function taskStatus(string $taskId)
    {
        try {
            $response = $this->api->getVirtualTryOnResult($taskId);
            $tryOn = VirtualTryOn::where('task_id', $taskId)->first();

            if (!$tryOn) {
                return response()->json(['error' => 'Task not found'], 404);
            }

            $status = $response['data']['task_status'];
            if ($status === 'succeed'){
                $tryOn->update(['status' => 'completed']);
            } else if ($status === 'failed') {
                $tryOn->update(['status' => 'failed']);
            } else {
                $tryOn->update(['status' => 'processing']);
            }

            /* $tryOn->update(['status' => $status === 'succeed' ? 'completed' : 'processing']); */

            if ($status === 'succeed') {
                $results = [];

                foreach ($response['data']['task_result']['images'] ?? [] as $image) {
                    try {
                        $results[] = $this->images->downloadAndSaveImage(
                            $image['url'],
                            'klingai/tryon_results',
                            'result_'
                        );
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                if ($results) {
                    $tryOn->update([
                        'result_image_paths' => $results,
                        'status' => 'completed',
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

    /**
     * Devuelve la imagen del modelo humano en base64 según el tipo de origen.
     *
     * @param Request $request
     * @return string Imagen en base64 sin prefijos
     */
    private function getHumanImage(Request $request): string
    {
        return match($request->model_source) {
            'upload' => $this->images->convertToBase64($request->file('human_image')),
            'virtual' => $this->images->getVirtualModelBase64(
                $request->selected_virtual_model,
                (int) $request->input('selected_virtual_index', 0)
            ),
            'default' => $this->images->getSelectedDefaultModelBase64($request->selected_default_model),
            default => throw new \Exception('Invalid model source')
        };
    }

    /**
     * Devuelve la imagen de la prenda en base64, combinando si son múltiples.
     *
     * @param Request $request
     * @return string Imagen en base64 sin prefijos
     */
    private function getGarmentImage(Request $request): string
    {
        return match($request->garment_type) {
            'single' => $this->images->convertToBase64($request->file('single_garment')),
            'multiple' => $this->images->combineImagesToBase64(
                $request->file('top_garment'),
                $request->file('bottom_garment')
            ),
            default => throw new \Exception('Invalid garment type')
        };
    }

    /**
     * Devuelve la ruta del modelo humano (para trazabilidad).
     * Si fue subido, la guarda en `storage/app`.
     * Si es default, se arma la ruta relativa.
     * Si es virtual, obtiene la ruta exacta según índice.
     *
     * @param Request $request
     * @return string|null Ruta interna en `storage`
     */
    private function getHumanPath(Request $request): ?string
    {
        if ($request->model_source === 'upload' && $request->hasFile('human_image')) {
            return $this->images->saveImage($request->file('human_image'), 'klingai/tryon_inputs/human', 'human_');
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

    /**
     * Devuelve la ruta de la prenda (para trazabilidad).
     * Si es una sola, la guarda.
     * Si son dos prendas, las combina y guarda la imagen combinada.
     *
     * @param Request $request
     * @return string|null Ruta interna en `storage`
     */
    private function getGarmentPath(Request $request): ?string
    {
        if ($request->garment_type === 'single' && $request->hasFile('single_garment')) {
            return $this->images->saveImage($request->file('single_garment'), 'klingai/tryon_inputs/garments', 'single_');
        }

        if ($request->garment_type === 'multiple' && $request->hasFile('top_garment') && $request->hasFile('bottom_garment')) {
            return $this->images->saveCombinedImage(
                $request->file('top_garment'),
                $request->file('bottom_garment'),
                'klingai/combined_garments'
            );
        }

        return null;
    }
}
