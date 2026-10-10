<?php

namespace App\Http\Controllers;

use App\Models\PricingRule;
use App\Models\VirtualModel;
use App\Models\VirtualTryOn;
use App\Services\GuidelinesService;
use App\Services\KlingAi\ImageProcessingService;
use App\Services\KlingAi\KlingApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VirtualTryOnController extends Controller
{
    private const MODEL_NAME = 'kling-virtual-try-on';
    private const OUTPUT_COUNT = 1;
    private const DIR_HUMAN = 'klingai/tryon_inputs/human';
    private const DIR_GARMENT = 'klingai/tryon_inputs/garments';

    public function __construct(
        private KlingApiService $api,
        private ImageProcessingService $images,
        private GuidelinesService $guidelines
    ) {}

    /**
     * Muestra la vista principal del módulo Virtual Try-On.
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
            'defaultModels'   => $defaultModels,
            'virtualModels'   => $virtualModels,
            'existingTryOns'  => $existingTryOns,
            'validModels'     => $guidelines['validModels'],
            'invalidModels'   => $guidelines['invalidModels'],
            'validGarments'   => $guidelines['validGarments'],
            'invalidGarments' => $guidelines['invalidGarments'],
        ]);
    }

    /**
     * Guarda las imágenes en storage (copias ajustadas a las reglas de Kling), las expone por URL pública
     * y crea la tarea en POST /solutions/virtual_try_on.
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'model_source'           => 'required|in:virtual,default,upload',
            'selected_default_model' => 'required_if:model_source,default',
            'selected_virtual_model' => 'required_if:model_source,virtual|exists:virtual_models,id',
            'selected_virtual_index'  => 'nullable|integer|min:0|max:3',
            'human_image'            => 'required_if:model_source,upload|file|image|mimes:jpg,jpeg,png|max:10240',
            'garment_type'           => 'required|in:single,multiple',
            'single_garment'         => 'required_if:garment_type,single|file|image|mimes:jpg,jpeg,png|max:10240',
            'top_garment'            => 'required_if:garment_type,multiple|file|image|mimes:jpg,jpeg,png|max:10240',
            'bottom_garment'         => 'required_if:garment_type,multiple|file|image|mimes:jpg,jpeg,png|max:10240',
        ]);

        $stored = []; // para limpiar si algo falla antes de crear el registro

        try {
            $baseUrl = $this->publicBaseUrl(); // falla pronto si no hay URL pública

            $humanPath = $this->storeHumanImage($request);
            $stored[] = $humanPath;

            $garmentPath = $this->storeGarmentImage($request);
            $stored[] = $garmentPath;

            $payload = [
                'contents' => [
                    ['type' => 'product_image', 'url' => 'https://media.istockphoto.com/id/1278802435/es/foto/su%C3%A9ter-de-color-amarillo-aislado-en-blanco-ropa-de-mujer-de-moda-ropa-de-punto.jpg?s=612x612&w=0&k=20&c=mlVhz-yJxZi5awTh8qxb2Z7Li7vU925Yue1Q1PIJOQE='],
                    ['type' => 'person_image',  'url' => 'https://img.magnific.com/fotos-premium/mujer-negocios-sonriente-equipo-sitio-web-vista-encima-fondo-blanco-cintura_935410-1390.jpg?semt=ais_hybrid&w=740&q=80'],
                ],
                'settings' => [
                    'keep_face'       => true,
                    'keep_pose'       => true,
                    'keep_background' => true,
                ],
                'options' => [
                    'watermark' => ['enabled' => false],
                ],
            ];

            $response = $this->api->createVirtualTryOn($payload, $humanPath, $garmentPath);

            $taskId = $response['data']['task_id'] ?? null;
            if (!$taskId) {
                throw new \Exception('Kling no devolvió un task_id');
            }

            $pricing = PricingRule::where('model_name', self::MODEL_NAME)
                ->where('mode', 'default')
                ->first();

            VirtualTryOn::create([
                'task_id'          => $taskId,
                'user_id'          => Auth::id() ?? '321123',
                'model_name'       => self::MODEL_NAME,
                'model_type'       => $request->model_source,
                'human_image_path' => $humanPath,
                'garments_type'    => $request->garment_type,
                'cloth_image_path' => $garmentPath,
                'output_count'     => self::OUTPUT_COUNT,
                'tokens'           => $pricing?->tokens ?? 0,
                'price'            => $pricing?->price ?? 0,
                'status'           => 'processing',
            ]);

            return response()->json($response);
        } catch (\Throwable $e) {
            foreach ($stored as $path) {
                Storage::disk('public')->delete($path);
            }

            Log::error('TryOn generate: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Consulta la tarea en Kling y devuelve al front el formato de siempre
     * (data.task_status / task_status_msg / local_images).
     * Si Kling o la descarga fallan de forma pasajera, la fila NO se marca como fallida.
     */
    public function taskStatus(string $taskId): JsonResponse
    {
        $tryOn = VirtualTryOn::where('task_id', $taskId)->first();

        if (!$tryOn) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        // Ya guardado: no volvemos a consultar a Kling ni a descargar
        if ($tryOn->status === 'completed' && !empty($tryOn->result_image_paths)) {
            return response()->json([
                'data' => [
                    'task_status'  => 'succeed',
                    'local_images' => array_map(fn($p) => Storage::url($p), $tryOn->result_image_paths),
                ],
            ]);
        }

        try {
            $response = $this->api->getVirtualTryOnResult($taskId);
        } catch (\Throwable $e) {
            Log::warning("TryOn {$taskId}: no se pudo consultar a Kling - " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 502);
        }

        $task = $this->extractTask($response);

        if (!$task) {
            Log::warning("TryOn {$taskId}: Kling no devolvió datos para la tarea", ['response' => $response]);

            return response()->json(['data' => [
                'task_status'     => 'failed',
                'task_status_msg' => 'Kling no encontró esta tarea',
            ]]);
        }

        $status = $task['status'] ?? 'processing';

        if ($status === 'failed') {
            $tryOn->update(['status' => 'failed']);

            return response()->json(['data' => [
                'task_status'     => 'failed',
                'task_status_msg' => $task['message'] ?? null,
            ]]);
        }

        if ($status !== 'succeed') {
            return response()->json(['data' => ['task_status' => $status]]); // submitted / processing
        }

        $paths = [];
        $lastError = null;

        foreach ($task['outputs'] ?? [] as $output) {
            if (($output['type'] ?? 'image') !== 'image' || empty($output['url'])) {
                continue;
            }

            try {
                $paths[] = $this->images->downloadAndSaveImage(
                    $output['url'],
                    'klingai/tryon_results',
                    'result_'
                );
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                Log::error("TryOn {$taskId}: no se pudo guardar una imagen - " . $lastError);
            }
        }

        if (!$paths) {
            // La fila queda en "processing" para reintentar en la próxima consulta
            return response()->json(['data' => [
                'task_status'     => 'failed',
                'task_status_msg' => $lastError
                    ? 'No se pudo guardar la imagen: ' . $lastError
                    : 'Kling no devolvió imágenes para esta tarea',
            ]]);
        }

        $tryOn->update(['result_image_paths' => $paths, 'status' => 'completed']);

        return response()->json(['data' => [
            'task_status'  => 'succeed',
            'local_images' => array_map(fn($p) => Storage::url($p), $paths),
        ]]);
    }

    // ---------------------------------------------------------------
    // Auxiliares
    // ---------------------------------------------------------------

    /**
     * La consulta devuelve `data` como objeto (Postman) o como lista (doc): se normaliza a un solo objeto.
     */
    private function extractTask(array $response): array
    {
        $data = $response['data'] ?? [];

        if (!is_array($data)) {
            return [];
        }

        return array_is_list($data) ? ($data[0] ?? []) : $data;
    }

    /**
     * Guarda una copia ajustada del modelo humano y retorna su ruta en el disco public.
     */
    private function storeHumanImage(Request $request): string
    {
        return match ($request->model_source) {
            'upload' => $this->images->storeForTryOn(
                $request->file('human_image'),
                self::DIR_HUMAN,
                'human_'
            ),
            'default' => $this->images->storeFileForTryOn(
                public_path('klingai/default_models/' . basename((string) $request->selected_default_model)),
                self::DIR_HUMAN,
                'human_'
            ),
            'virtual' => $this->storeVirtualModelImage($request),
            default   => throw new \Exception('Invalid model source'),
        };
    }

    private function storeVirtualModelImage(Request $request): string
    {
        $model = VirtualModel::find($request->selected_virtual_model);
        $index = (int) $request->input('selected_virtual_index', 0);
        $paths = $model?->result_image_paths ?? [];

        if (!$model || $model->status !== 'completed' || !isset($paths[$index])) {
            throw new \Exception('El modelo virtual seleccionado no tiene imagen disponible.');
        }

        return $this->images->storeFileForTryOn(
            storage_path('app/public/' . $paths[$index]),
            self::DIR_HUMAN,
            'human_'
        );
    }

    /**
     * Guarda la prenda. Si son dos, las combina en una sola imagen (la API solo acepta una).
     */
    private function storeGarmentImage(Request $request): string
    {
        if ($request->garment_type === 'single') {
            return $this->images->storeForTryOn(
                $request->file('single_garment'),
                self::DIR_GARMENT,
                'single_'
            );
        }

        return $this->images->storeCombinedForTryOn(
            $request->file('top_garment'),
            $request->file('bottom_garment'),
            self::DIR_GARMENT,
            'combined_'
        );
    }

    /**
     * Base de las URLs que Kling va a descargar. Debe ser pública (no localhost ni red privada).
     */
    private function publicBaseUrl(): string
    {
        $base = rtrim((string) (config('services.kling.public_url') ?: config('app.url')), '/');
        $host = (string) parse_url($base, PHP_URL_HOST);

        $isLocalName = $host === '' || $host === 'localhost'
            || str_ends_with($host, '.localhost')
            || str_ends_with($host, '.test')
            || str_ends_with($host, '.local');

        $isPrivateIp = filter_var($host, FILTER_VALIDATE_IP)
            && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);

        if ($isLocalName || $isPrivateIp) {
            throw new \Exception(
                'Kling necesita descargar las imágenes desde una URL pública. '
                    . 'Configura KLING_PUBLIC_URL en el .env con tu dominio o un túnel (por ejemplo ngrok).'
            );
        }

        return $base;
    }

    private function publicUrl(string $baseUrl, string $path): string
    {
        $encoded = implode('/', array_map('rawurlencode', explode('/', $path)));

        return $baseUrl . '/storage/' . $encoded;
    }
}
