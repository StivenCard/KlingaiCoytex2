<?php

namespace App\Http\Controllers;

use App\Models\ImageToVideo;
use App\Models\VirtualModel;
use App\Models\VirtualTryOn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminGenerationsController extends Controller
{
    public function index(Request $request)
    {
        // Parámetro opcional para filtrar por usuario
        $userId = $request->input('user_id');

        // Calcular totales con o sin filtro
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

        // Obtener generaciones con filtro opcional
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

        return view('admin.generations', compact('totals', 'generations', 'userId'));
    }


    public function deleteGeneration(Request $request, $type, $id)
    {
        try {
            Log::info("Iniciando eliminación de generación", ['type' => $type, 'id' => $id]);

            DB::beginTransaction();

            switch ($type) {
                case 'video':
                    $item = ImageToVideo::findOrFail($id);

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
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al eliminar la generación: ' . $e->getMessage()
            ], 500);
        }
    }


    public function deleteAllGenerations()
    {
        try {
            DB::beginTransaction();

            // Eliminar todos los archivos de ImageToVideo
            $videos = ImageToVideo::all();
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
            $tryons = VirtualTryOn::all();
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
            $models = VirtualModel::all();
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

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Todas las generaciones han sido eliminadas'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar todas las generaciones: ' . $e->getMessage());

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
