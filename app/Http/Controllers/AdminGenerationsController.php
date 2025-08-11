<?php

namespace App\Http\Controllers;

use App\Models\ImageToVideo;
use App\Models\VirtualModel;
use App\Models\VirtualTryOn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\KlingAi\KlingApiService;

class AdminGenerationsController extends Controller
{
     private $klingService;

    public function __construct(KlingApiService $klingService)
    {
        $this->klingService = $klingService;
    }

    public function index(Request $request)
    {
        // Obtener el consumo de la API
        try {
            $apiConsumption = $this->klingService->getApiConsumption();
            $resourcePacks = $apiConsumption['data']['resource_pack_subscribe_infos'] ?? [];
        } catch (\Exception $e) {
            $resourcePacks = [];
            Log::error("Error obteniendo consumo de API: " . $e->getMessage());
        }

        // Código existente para obtener generaciones...
        $userId = $request->input('user_id');
        if ($userId && !is_numeric($userId)) {
            return redirect()->back()->withErrors(['user_id' => 'El ID de usuario debe ser un número válido']);
        }

        $totals = [
            'videos' => ImageToVideo::when($userId, fn($q) => $q->where('user_id', $userId))
                ->where('status', 'completed')
                ->count(),
            'tryons' => VirtualTryOn::when($userId, fn($q) => $q->where('user_id', $userId))
                ->where('status', 'completed')
                ->count(),
            'models' => VirtualModel::when($userId, fn($q) => $q->where('user_id', $userId))
                ->where('status', 'completed')
                ->count(),
        ];

        $generations = [
            'videos' => ImageToVideo::when($userId, fn($q) => $q->where('user_id', $userId))
                ->where('status', '!=', 'failed')
                ->latest()
                ->get(),
            'tryons' => VirtualTryOn::when($userId, fn($q) => $q->where('user_id', $userId))
                ->where('status', '!=', 'failed')
                ->latest()
                ->get(),
            'models' => VirtualModel::when($userId, fn($q) => $q->where('user_id', $userId))
                ->where('status', '!=', 'failed')
                ->latest()
                ->get(),
        ];

        return view('admin.generations', compact('totals', 'generations', 'userId', 'resourcePacks'));
    }

    public function deleteGeneration(Request $request, $type, $id)
    {
        try {
            // Obtener user_id del request para validación adicional si es necesario
            $userId = $request->input('user_id');

            Log::info("Iniciando eliminación de generación", [
                'type' => $type,
                'id' => $id,
                'user_id' => $userId
            ]);

            DB::beginTransaction();

            switch ($type) {
                case 'video':
                    $item = ImageToVideo::findOrFail($id);

                    // Validación opcional: verificar que pertenece al usuario
                    if ($userId && $item->user_id != $userId) {
                        throw new \Exception('No tienes permisos para eliminar esta generación');
                    }

                    // Eliminar videos resultantes
                    if (!empty($item->result_video_paths)) {
                        foreach ($item->result_video_paths as $path) {
                            $this->deleteFile($path);
                        }
                    }

                    // Eliminar imágenes de entrada
                    if (!empty($item->input_image_paths)) {
                        foreach ($item->input_image_paths as $path) {
                            $this->deleteFile($path);
                        }
                    }

                    $item->delete();
                    break;

                case 'tryon':
                    $item = VirtualTryOn::findOrFail($id);

                    // Validación opcional: verificar que pertenece al usuario
                    if ($userId && $item->user_id != $userId) {
                        throw new \Exception('No tienes permisos para eliminar esta generación');
                    }

                    // Eliminar imágenes resultantes
                    if (!empty($item->result_image_paths)) {
                        foreach ($item->result_image_paths as $path) {
                            $this->deleteFile($path);
                        }
                    }

                    // Eliminar imagen humana
                    if ($item->human_image_path) {
                        $this->deleteFile($item->human_image_path);
                    }

                    // Eliminar imagen de ropa
                    if ($item->cloth_image_path) {
                        $this->deleteFile($item->cloth_image_path);
                    }

                    $item->delete();
                    break;

                case 'model':
                    $item = VirtualModel::findOrFail($id);

                    // Validación opcional: verificar que pertenece al usuario
                    if ($userId && $item->user_id != $userId) {
                        throw new \Exception('No tienes permisos para eliminar esta generación');
                    }

                    // Eliminar imágenes resultantes
                    if (!empty($item->result_image_paths)) {
                        foreach ($item->result_image_paths as $path) {
                            $this->deleteFile($path);
                        }
                    }

                    $item->delete();
                    break;

                default:
                    throw new \Exception('Tipo de generación no válido');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Generación eliminada con éxito'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al eliminar generación: " . $e->getMessage(), [
                'type' => $type,
                'id' => $id,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al eliminar la generación: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteAllGenerations(Request $request)
    {
        try {
            // Obtener user_id del request (opcional)
            $userId = $request->input('user_id');

            Log::info("Iniciando eliminación masiva de generaciones", ['user_id' => $userId]);

            DB::beginTransaction();

            if ($userId) {
                // Eliminar solo las generaciones del usuario específico
                $videos = ImageToVideo::where('user_id', $userId)->get();
                $tryons = VirtualTryOn::where('user_id', $userId)->get();
                $models = VirtualModel::where('user_id', $userId)->get();

                // Eliminar archivos y registros de ImageToVideo del usuario específico
                foreach ($videos as $video) {
                    if (!empty($video->result_video_paths)) {
                        foreach ($video->result_video_paths as $path) {
                            $this->deleteFile($path);
                        }
                    }
                    if (!empty($video->input_image_paths)) {
                        foreach ($video->input_image_paths as $path) {
                            $this->deleteFile($path);
                        }
                    }
                }
                ImageToVideo::where('user_id', $userId)->delete();

                // Eliminar archivos y registros de VirtualTryOn del usuario específico
                foreach ($tryons as $tryon) {
                    if (!empty($tryon->result_image_paths)) {
                        foreach ($tryon->result_image_paths as $path) {
                            $this->deleteFile($path);
                        }
                    }
                    if ($tryon->human_image_path) {
                        $this->deleteFile($tryon->human_image_path);
                    }
                    if ($tryon->cloth_image_path) {
                        $this->deleteFile($tryon->cloth_image_path);
                    }
                }
                VirtualTryOn::where('user_id', $userId)->delete();

                // Eliminar archivos y registros de VirtualModel del usuario específico
                foreach ($models as $model) {
                    if (!empty($model->result_image_paths)) {
                        foreach ($model->result_image_paths as $path) {
                            $this->deleteFile($path);
                        }
                    }
                }
                VirtualModel::where('user_id', $userId)->delete();

                $message = "Todas las generaciones del usuario {$userId} han sido eliminadas";

            } else {
                // Eliminar TODAS las generaciones de todos los usuarios
                $videos = ImageToVideo::all();
                $tryons = VirtualTryOn::all();
                $models = VirtualModel::all();

                // Eliminar todos los archivos de ImageToVideo
                foreach ($videos as $video) {
                    if (!empty($video->result_video_paths)) {
                        foreach ($video->result_video_paths as $path) {
                            $this->deleteFile($path);
                        }
                    }
                    if (!empty($video->input_image_paths)) {
                        foreach ($video->input_image_paths as $path) {
                            $this->deleteFile($path);
                        }
                    }
                }

                // Eliminar todos los archivos de VirtualTryOn
                foreach ($tryons as $tryon) {
                    if (!empty($tryon->result_image_paths)) {
                        foreach ($tryon->result_image_paths as $path) {
                            $this->deleteFile($path);
                        }
                    }
                    if ($tryon->human_image_path) {
                        $this->deleteFile($tryon->human_image_path);
                    }
                    if ($tryon->cloth_image_path) {
                        $this->deleteFile($tryon->cloth_image_path);
                    }
                }

                // Eliminar todos los archivos de VirtualModel
                foreach ($models as $model) {
                    if (!empty($model->result_image_paths)) {
                        foreach ($model->result_image_paths as $path) {
                            $this->deleteFile($path);
                        }
                    }
                }

                // Eliminar registros de la base de datos
                ImageToVideo::truncate();
                VirtualTryOn::truncate();
                VirtualModel::truncate();

                $message = 'Todas las generaciones han sido eliminadas';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar generaciones: ' . $e->getMessage(), [
                'user_id' => $userId
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al eliminar generaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    private function deleteFile($path)
    {
        if (empty($path)) {
            return;
        }

        try {
            // Eliminar cualquier 'storage/' del inicio del path si existe
            $path = preg_replace('/^storage\//', '', $path);

            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                Log::info("Archivo eliminado con éxito: {$path}");
            } else {
                Log::warning("Archivo no encontrado: {$path}");
            }
        } catch (\Exception $e) {
            Log::error("Error al eliminar archivo: {$path}", ['error' => $e->getMessage()]);
        }
    }
}
