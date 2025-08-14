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

    /**
     * Muestra el dashboard de generaciones del administrador.
     */
    public function index(Request $request)
    {
        // Obtener el consumo de la API
        try {
            $apiConsumption = $this->klingService->getApiConsumption();
            $resourcePacks = $this->processApiConsumption($apiConsumption['data']['resource_pack_subscribe_infos'] ?? []);
        } catch (\Exception $e) {
            $resourcePacks = [];
            Log::error("Error obteniendo consumo de API: " . $e->getMessage());
        }

        // Obtener el user_id del request para filtrar todas las tablas
        $userIdFilter = $request->input('user_id');

        // Se modificó el llamado a getData para usar un solo user_id
        $data = [
            'videos' => $this->getData(ImageToVideo::class, $userIdFilter),
            'tryons' => $this->getData(VirtualTryOn::class, $userIdFilter),
            'models' => $this->getData(VirtualModel::class, $userIdFilter),
        ];

        $totals = [
            'videos' => $data['videos']['count'],
            'tryons' => $data['tryons']['count'],
            'models' => $data['models']['count'],
        ];

        // Se pasa el filtro a la vista para mantener el valor del campo
        return view('admin.generations', compact('data', 'totals', 'resourcePacks', 'userIdFilter'));
    }

    /**
     * Procesa los datos de consumo de la API para la vista.
     */
    private function processApiConsumption(array $packs): array
    {
        foreach ($packs as &$pack) {
            $pack['used_quantity'] = $pack['total_quantity'] - $pack['remaining_quantity'];
            $pack['percentage_used'] = $pack['total_quantity'] > 0
                ? ($pack['used_quantity'] / $pack['total_quantity']) * 100
                : 0;

            $now = new \DateTime();
            $purchaseDate = new \DateTime('@' . ($pack['purchase_time'] / 1000));
            $daysActive = $purchaseDate->diff($now)->days ?: 1;

            $pack['daily_usage'] = $pack['used_quantity'] / $daysActive;
            $pack['formatted_dates'] = [
                'purchase' => $purchaseDate->format('d/m/Y H:i:s'),
                'effective' => date('d/m/Y H:i:s', $pack['effective_time'] / 1000),
                'expiration' => date('d/m/Y H:i:s', $pack['invalid_time'] / 1000),
            ];
        }

        return $packs;
    }

    /**
     * Obtiene los datos de una tabla de generación, opcionalmente filtrando por usuario.
     */
    private function getData(string $modelClass, ?string $userId = null): array
    {
        $query = $modelClass::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return [
            'items' => (clone $query)->where('status', '!=', 'failed')->latest()->get(),
            'count' => (clone $query)->where('status', 'completed')->count(),
        ];
    }

    /**
     * Encuentra un item de generación por su tipo y ID.
     */
    private function findGenerationItem(string $type, int $id)
    {
        return match ($type) {
            'video' => ImageToVideo::findOrFail($id),
            'tryon' => VirtualTryOn::findOrFail($id),
            'model' => VirtualModel::findOrFail($id),
            default => throw new \Exception('Tipo de generación no válido'),
        };
    }

    /**
     * Elimina todos los archivos asociados a un item de generación.
     */
    private function deleteGenerationFiles($item)
    {
        if ($item instanceof ImageToVideo) {
            $this->deleteFile($item->result_video_paths);
            $this->deleteFile($item->input_image_paths);
        } elseif ($item instanceof VirtualTryOn) {
            $this->deleteFile($item->result_image_paths);
            $this->deleteFile($item->human_image_path);
            $this->deleteFile($item->cloth_image_path);
        } elseif ($item instanceof VirtualModel) {
            $this->deleteFile($item->result_image_paths);
        }
    }

    /**
     * Elimina una generación específica y sus archivos asociados.
     */
    public function deleteGeneration(Request $request, $type, $id)
    {
        try {
            DB::beginTransaction();

            $item = $this->findGenerationItem($type, $id);
            $userId = $request->input('user_id');

            // Validación de permisos. El admin puede eliminar todo,
            // pero si se especifica un user_id, se verifica que coincida.
            if ($userId && $item->user_id != $userId) {
                throw new \Exception('No tienes permisos para eliminar esta generación');
            }

            $this->deleteGenerationFiles($item);
            $item->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Generación eliminada con éxito'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al eliminar generación", [
                'type' => $type,
                'id' => $id,
                'user_id' => $request->input('user_id'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina todas las generaciones de un tipo o de todos los tipos.
     */
    public function deleteAllGenerations(Request $request)
    {
        try {
            DB::beginTransaction();

            $type = $request->input('type');
            $userId = $request->input('user_id');
            $message = '';

            if ($type) {
                $this->deleteAllByType($type, $userId);
                $message = "Todas las generaciones de tipo $type han sido eliminadas";
            } else {
                $this->deleteAllTypes($userId);
                $message = $userId
                    ? "Todas las generaciones del usuario $userId han sido eliminadas"
                    : 'Todas las generaciones han sido eliminadas';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar generaciones: ' . $e->getMessage(), [
                'type' => $type ?? 'all',
                'user_id' => $userId
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al eliminar generaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina todas las generaciones de un tipo específico y sus archivos.
     */
    private function deleteAllByType(string $type, ?string $userId = null): void
    {
        $query = match ($type) {
            'video' => ImageToVideo::query(),
            'tryon' => VirtualTryOn::query(),
            'model' => VirtualModel::query(),
            default => throw new \Exception('Tipo de generación no válido'),
        };

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $query->each(function ($item) {
            $this->deleteGenerationFiles($item);
            $item->delete();
        });
    }

    /**
     * Elimina todas las generaciones de todos los tipos.
     */
    private function deleteAllTypes(?string $userId = null): void
    {
        $this->deleteAllByType('video', $userId);
        $this->deleteAllByType('tryon', $userId);
        $this->deleteAllByType('model', $userId);
    }

    /**
     * Elimina un archivo o una lista de archivos del disco.
     */
    private function deleteFile($path)
    {
        if (is_array($path)) {
            foreach ($path as $p) {
                $this->deleteFile($p);
            }
            return;
        }

        if (empty($path)) {
            return;
        }

        try {
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
