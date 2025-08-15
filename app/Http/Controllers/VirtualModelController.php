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
    /**
     * Constructor del controlador de modelos virtuales.
     *
     * @param KlingApiService $api Servicio para interactuar con la API de KlingAI.
     * @param ImageProcessingService $images Servicio para procesar imágenes (base64, descarga, validación, etc).
     * @param HintsService $hints Servicio que entrega prompts predefinidos.
     */
    public function __construct(
        private KlingApiService $api,
        private ImageProcessingService $images,
        private HintsService $hints
    ) {}

    /**
     * Muestra la vista principal de generación de modelos virtuales.
     *
     * Carga:
     * - Los prompts sugeridos (hints).
     * - Los últimos 20 modelos generados.
     *
     * @return View
     */
    public function show()
    {
        $hints = $this->hints->getAllHints();
        $existingModels = VirtualModel::latest()->take(20)->get();

        return view('virtual-model', compact('hints', 'existingModels'));
    }

    /**
     * Envía una solicitud de generación de modelo virtual a KlingAI.
     *
     * Valida los datos del formulario, genera el prompt (si no fue personalizado)
     * y registra en base de datos el modelo con estado "processing".
     *
     * @param Request $request Datos validados del formulario.
     * @return JsonResponse Respuesta de la API o error.
     */
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

            $response = $this->api->createImageGenerationTask([
                'model_name' => 'kling-v1-5',
                'prompt' => $prompt,
                'aspect_ratio' => $request->aspect_ratio,
                'n' => $request->output_count,
                'gender' => $request->gender,
                'age_group' => $request->age_group,
                'skin_tone' => $request->skin_tone,
            ]);

            if ($taskId = $response['data']['task_id'] ?? null) {
                VirtualModel::create([
                    'task_id' => $taskId,
                    'user_id' => '123321',
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

    /**
     * Consulta el estado de una tarea de modelo virtual en KlingAI y guarda imágenes si está lista.
     *
     * Si el estado es `succeed`, descarga las imágenes, las guarda en disco y
     * actualiza el modelo en base de datos.
     *
     * @param string $taskId ID de la tarea a consultar.
     * @return JsonResponse Resultado actualizado o error.
     */
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
                    $model->update([
                        'result_image_paths' => $results,
                        'status' => 'completed'
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
     * Construye el prompt a partir del formulario.
     *
     * Si el usuario ingresó texto, lo devuelve limpio.
     * Si no, genera uno automáticamente con base en género, edad y tono de piel.
     *
     * @param Request $request Datos del formulario.
     * @return string Prompt final.
     */
    private function buildPrompt(Request $request): string
    {
        if ($request->filled('prompt')) {
            return trim($request->prompt);
        }

        return $this->buildBasePrompt($request);
    }

    /**
     * Genera un prompt predeterminado en español para la generación del modelo.
     *
     * @param Request $request
     * @return string Prompt generado.
     */
    private function buildBasePrompt(Request $request): string
    {
        $gender = $request->gender === 'male' ? 'masculino' : 'femenino';
        $age = $this->mapAge($request->age_group);
        $skinTone = $this->mapSkinTone($request->skin_tone);

        return "Crear un modelo de cuerpo completo, con rasgos colombianos, fondo sencillo, sin imperfecciones, cuerpo completo, sin irregularidades, de genero {$gender}, edad {$age}, tono de piel {$skinTone}.";
    }

    /**
     * Traduce el grupo de edad técnico a un texto legible para prompt.
     *
     * @param string $age Valor interno: children, youth, elderly.
     * @return string Valor legible: joven, adulto joven, adulto mayor.
     */
    private function mapAge(string $age): string
    {
        return match($age) {
            'children' => 'joven',
            'youth' => 'adulto joven',
            'elderly' => 'adulto mayor',
            default => 'adulto'
        };
    }

    /**
     * Traduce el tono de piel técnico a su versión legible.
     *
     * @param string $tone Valor interno: light, medium, dark, olive.
     * @return string Valor legible: claro, medio, oscuro, oliva.
     */
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
