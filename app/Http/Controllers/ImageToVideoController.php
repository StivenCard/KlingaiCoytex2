<?php

namespace App\Http\Controllers;

use App\Models\ImageToVideo;
use App\Models\VirtualTryOn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\KlingAi\KlingApiService;
use App\Services\KlingAi\ImageProcessingService;

class ImageToVideoController extends Controller
{
    /**
     * Constructor del controlador de Image to Video.
     *
     * @param KlingApiService $api Servicio para interactuar con la API de KlingAI.
     * @param ImageProcessingService $images Servicio para procesar imágenes.
     */
    public function __construct(
        private KlingApiService $api,
        private ImageProcessingService $images
    ) {}

    /**
     * Muestra la vista principal de generación de videos desde imágenes.
     *
     * @return View
     */
    public function show()
    {
        $virtualTryOns = VirtualTryOn::where('status', 'completed')
            ->latest()
            ->take(20)
            ->get();

        $existingVideos = ImageToVideo::where('status', '!=', 'failed')
            ->latest()
            ->take(20)
            ->get();

        return view('image-to-video', compact('virtualTryOns', 'existingVideos'));
    }

    /**
     * Procesa una solicitud para generar un video a partir de múltiples imágenes.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generate(Request $request)
    {
        $request->validate([
            'images.*' => 'required|file|image|mimes:jpg,jpeg,png|max:10240',
            'images' => 'required|array|min:1|max:4',
            'prompt' => 'required|string|max:2500',
            'negative_prompt' => 'nullable|string|max:2500',
            'mode' => 'nullable|in:std,pro',
            'duration' => 'nullable|in:5,10',
            'aspect_ratio' => 'nullable|in:16:9,9:16,1:1',
        ]);

        try {
            $imageList = [];
            $imagePaths = [];

            // Procesar cada imagen
            foreach ($request->file('images') as $image) {
                // Guardar imagen para trazabilidad
                $path = $this->images->saveImage($image, 'klingai/video_inputs', 'input_');
                $imagePaths[] = $path;

                // Convertir a base64 para la API
                $imageList[] = [
                    'image' => $this->images->convertToBase64($image)
                ];
            }

            // Preparar payload para la API
            $payload = [
                'model_name' => 'kling-v1-6',
                'image_list' => $imageList,
                'prompt' => $request->prompt,
                'mode' => $request->input('mode', 'std'),
                'duration' => $request->input('duration', '5'),
                'aspect_ratio' => $request->input('aspect_ratio', '16:9'),
            ];

            if ($request->filled('negative_prompt')) {
                $payload['negative_prompt'] = $request->negative_prompt;
            }

            // Llamar a la API
            $response = $this->api->createMultiImageToVideo($payload);

            // Guardar registro en base de datos
            if ($taskId = $response['data']['task_id'] ?? null) {
                ImageToVideo::create([
                    'task_id' => $taskId,
                    'model_name' => 'kling-v1-6',
                    'prompt' => $request->prompt,
                    'negative_prompt' => $request->negative_prompt,
                    'mode' => $request->input('mode', 'std'),
                    'duration' => $request->input('duration', '5'),
                    'aspect_ratio' => $request->input('aspect_ratio', '16:9'),
                    'input_image_paths' => $imagePaths,
                    'status' => 'processing',
                ]);
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Consulta el estado de una tarea de generación de video.
     *
     * @param string $taskId
     * @return JsonResponse
     */
    public function taskStatus(string $taskId)
    {
        try {
            $response = $this->api->getMultiImageToVideoResult($taskId);
            $videoTask = ImageToVideo::where('task_id', $taskId)->first();

            if (!$videoTask) {
                return response()->json(['error' => 'Task not found'], 404);
            }

            $status = $response['data']['task_status'];
            $videoTask->update([
                'status' => match($status) {
                    'succeed' => 'completed',
                    'failed' => 'failed',
                    default => 'processing',
                }
            ]);

            if ($status === 'succeed') {
                $results = [];

                foreach ($response['data']['task_result']['videos'] ?? [] as $video) {
                    try {
                        $results[] = $this->images->downloadAndSaveVideo(
                            $video['url'],
                            'klingai/video_results',
                            'video_'
                        );
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                if ($results) {
                    $videoTask->update([
                        'result_video_paths' => $results,
                        'status' => 'completed'
                    ]);

                    $response['data']['local_videos'] = array_map(
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
}
