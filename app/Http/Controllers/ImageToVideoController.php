<?php

namespace App\Http\Controllers;

use App\Models\ImageToVideo;
use App\Models\PricingRule;
use App\Models\VirtualTryOn;
use App\Services\HintsService;
use App\Services\KlingAi\ImageProcessingService;
use App\Services\KlingAi\KlingApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ImageToVideoController extends Controller
{
    public function __construct(
        private KlingApiService $api,
        private ImageProcessingService $images,
        private HintsService $hints
    ) {}

    public function show()
    {
        $virtualTryOns = VirtualTryOn::where('status', 'completed')->latest()->take(20)->get();
        $existingVideos = ImageToVideo::where('status', '!=', 'failed')->latest()->take(20)->get();

        return view('image-to-video', [
            'virtualTryOns' => $virtualTryOns,
            'existingVideos' => $existingVideos,
            'hintsPrompts' => $this->hints->getAllPromptVideo(),
            'hintsNegative' => $this->hints->getAllNegativePrompts()
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            //images es un array de imágenes, mínimo 1 y máximo 4.
            'images' => 'required|array|min:1|max:4',
            //images.* valida cada imagen individualmente
            'images.*' => 'required|image|mimes:jpg,jpeg,png|max:10240',
            'prompt' => 'required|string|max:2500',
            'negative_prompt' => 'nullable|string|max:2500',
            'mode' => 'nullable|in:std,pro',
            'duration' => 'nullable|in:5,10',
            'aspect_ratio' => 'nullable|in:16:9,9:16,1:1',
        ]);

        try {
            [$imageList, $imagePaths] = $this->prepareImages($request->file('images'));

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

            $response = $this->api->createMultiImageToVideo($payload);

            if ($taskId = $response['data']['task_id'] ?? null) {

                $pricing = PricingRule::where('model_name', 'kling-v1-6-multi-image')
                ->where('mode', $payload['mode'])
                ->where('duration', $payload['duration'])
                ->first();

                ImageToVideo::create([
                    'task_id' => $taskId,
                    'user_id' => Auth::id() ?? '123456',
                    'model_name' => 'kling-v1-6',
                    'prompt' => $request->prompt,
                    'negative_prompt' => $request->negative_prompt,
                    'mode' => $payload['mode'],
                    'duration' => $payload['duration'],
                    'aspect_ratio' => $payload['aspect_ratio'],
                    'input_image_paths' => $imagePaths,
                    'tokens' => $pricing->tokens,
                    'price' => $pricing->price,
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
            $response = $this->api->getMultiImageToVideoResult($taskId);
            $videoTask = ImageToVideo::where('task_id', $taskId)->first();

            if (!$videoTask) {
                return response()->json(['error' => 'Task not found'], 404);
            }

            $status = $response['data']['task_status'];
            $videoTask->update(['status' => $this->mapStatus($status)]);

            if ($status === 'succeed') {
                $videoPaths = collect($response['data']['task_result']['videos'] ?? [])
                    ->map(fn($video) => $this->images->downloadAndSaveVideo(
                        $video['url'], 'klingai/video_results', 'video_'
                    ))
                    ->filter()
                    ->values()
                    ->toArray();

                if ($videoPaths) {
                    $videoTask->update([
                        'result_video_paths' => $videoPaths,
                        'status' => 'completed'
                    ]);

                    $response['data']['local_videos'] = array_map(fn($p) => Storage::url($p), $videoPaths);
                }
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Procesa imágenes subidas: guarda en disco y las convierte a base64.
     */
    private function prepareImages(array $images): array
    {
        $imageList = [];
        $imagePaths = [];

        foreach ($images as $image) {
            $path = $this->images->saveImage($image, 'klingai/video_inputs', 'input_');
            $imagePaths[] = $path;
            $imageList[] = ['image' => $this->images->convertToBase64($image)];
        }

        return [$imageList, $imagePaths];
    }

    /**
     * Mapea el estado de Kling a estados locales.
     */
    private function mapStatus(string $status): string
    {
        return match($status) {
            'succeed' => 'completed',
            'failed' => 'failed',
            default => 'processing',
        };
    }
}
