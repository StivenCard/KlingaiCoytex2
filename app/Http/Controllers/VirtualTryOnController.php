<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\KlingAi\KlingApiService;
use App\Services\KlingAi\ImageProcessingService;
use Illuminate\Support\Facades\Log;

class VirtualTryOnController extends Controller
{
    protected $apiService;
    protected $imageService;

    public function __construct(KlingApiService $apiService, ImageProcessingService $imageService)
    {
        $this->apiService = $apiService;
        $this->imageService = $imageService;
    }

    public function show()
    {
        return view('virtual-try-on');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'model_source' => 'required|in:virtual,default,upload',
            'human_image' => 'required_if:model_source,upload|file|image|max:10240',
            'garment_type' => 'required|in:single,multiple',
            'single_garment' => 'required_if:garment_type,single|file|image|max:10240',
            'top_garment' => 'required_if:garment_type,multiple|file|image|max:10240',
            'bottom_garment' => 'required_if:garment_type,multiple|file|image|max:10240',
            'output_count' => 'required|integer|min:1|max:4',
        ]);

        try {
            // Procesar imagen del modelo humano
            $humanImage = $this->processHumanImage($request);

            // Procesar imagen de prenda
            $clothImage = $this->processGarments($request);

            // Payload para la API
            $payload = [
                'model_name' => 'kolors-virtual-try-on-v1-5',
                'human_image' => $humanImage,
                'cloth_image' => $clothImage,
                'n' => (int) $request->input('output_count', 1),
            ];

            // Llamada a la API
            $response = $this->apiService->virtualTryOn($payload);

            // Log de request y response
            Log::channel('api')->info('Virtual Try-On Request', [
                'payload' => $payload,
                'response' => $response,
                'timestamp' => now(),
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Virtual Try-On Error', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function taskStatus($taskId)
    {
        try {
            $response = $this->apiService->checkTaskStatus($taskId);
            Log::channel('api')->info('Virtual Try-On Status', [
                'task_id' => $taskId,
                'response' => $response,
                'timestamp' => now(),
            ]);
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Virtual Try-On Status Error', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    private function processHumanImage(Request $request)
    {
        if ($request->model_source === 'upload') {
            return $this->imageService->convertToBase64($request->file('human_image'));
        }
        if ($request->model_source === 'virtual') {
            // Aquí deberías obtener el base64 de un modelo virtual generado previamente
            // Por simplicidad, retorna un string de ejemplo
            return 'base64-string-of-virtual-model';
        }
        // Default model
        return $this->getDefaultModel();
    }

    private function processGarments(Request $request)
    {
        if ($request->garment_type === 'single') {
            return $this->imageService->convertToBase64($request->file('single_garment'));
        }
        // Multiple garments: combinar horizontalmente
        return $this->imageService->combineImagesToBase64(
            $request->file('top_garment'),
            $request->file('bottom_garment')
        );
    }

    private function getDefaultModel()
    {
        // Retorna el base64 de una imagen predefinida (puedes cargarla desde storage o public)
        $path = public_path('default-models/model1.png');
        $image = \Intervention\Image\Facades\Image::make($path);
        return base64_encode($image->encode('png'));
    }
}
