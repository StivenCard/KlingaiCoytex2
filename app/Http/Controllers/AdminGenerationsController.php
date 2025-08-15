<?php

namespace App\Http\Controllers;

use App\Models\ImageToVideo;
use App\Models\VirtualModel;
use App\Models\VirtualTryOn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        // 1. Obtener datos de consumo de API
        try {
            $apiConsumption = $this->klingService->getApiConsumption();
            $resourcePacks = $apiConsumption['data']['resource_pack_subscribe_infos'] ?? [];
            print_r($resourcePacks);

            // NUEVA LÓGICA: Comprueba si la lista de paquetes está vacía
            if (empty($resourcePacks)) {
                session()->flash('info_message', 'No hay paquetes de recursos activos o disponibles en su cuenta.');
            }

        } catch (\Exception $e) {
            // La API falló, registra el error y prepara la vista
            Log::error("Error obteniendo consumo de API: " . $e->getMessage());
            $resourcePacks = [];
            
            // Esta línea ya la tenías, captura errores de conexión/autenticación
            session()->flash('api_error', 'Error de la API: ' . $e->getMessage());
        }

        // 2. Procesar parámetros de filtrado
        $filters = $this->processFilters($request);

        // 3. Obtener datos para cada tabla independientemente
        $data = [
            'videos' => $this->getVideoData($filters['video_user_id'] ?? null),
            'tryons' => $this->getTryOnData($filters['tryon_user_id'] ?? null),
            'models' => $this->getModelData($filters['model_user_id'] ?? null),
        ];

        // 4. Calcular totales generales
        $totals = [
            'videos' => $data['videos']['count'],
            'tryons' => $data['tryons']['count'],
            'models' => $data['models']['count'],
        ];

        return view('admin.generations', compact('data', 'totals', 'resourcePacks', 'filters'));
    }

    private function processApiConsumption(array $packs): array
    {
        foreach ($packs as &$pack) {
            $pack['used_quantity'] = $pack['total_quantity'] - $pack['remaining_quantity'];
            $pack['percentage_used'] = $pack['total_quantity'] > 0
                ? ($pack['used_quantity'] / $pack['total_quantity']) * 100
                : 0;

            $now = new \DateTime();
            $purchaseDate = new \DateTime('@' . ($pack['purchase_time']/1000));
            $daysActive = $purchaseDate->diff($now)->days ?: 1;

            $pack['daily_usage'] = $pack['used_quantity'] / $daysActive;
            $pack['formatted_dates'] = [
                'purchase' => $purchaseDate->format('d/m/Y H:i:s'),
                'effective'  => date('d/m/Y H:i:s', $pack['effective_time'] / 1000),
                'expiration' => date('d/m/Y H:i:s', $pack['invalid_time'] / 1000),
            ];
        }

        return $packs;
    }

    private function processFilters(Request $request): array
    {
        return [
            'video_user_id' => $request->input('video_user_id'),
            'tryon_user_id' => $request->input('tryon_user_id'),
            'model_user_id' => $request->input('model_user_id'),
        ];
    }

    private function getVideoData(?string $userId = null): array
    {
        $query = ImageToVideo::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId));
        
        return [
            'items' => $query->where('status', '!=', 'failed')->latest()->get(),
            'count' => $query->where('status', 'completed')->count(),
        ];
    }

    private function getTryOnData(?string $userId = null): array
    {
        $query = VirtualTryOn::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId));
        
        return [
            'items' => $query->where('status', '!=', 'failed')->latest()->get(),
            'count' => $query->where('status', 'completed')->count(),
        ];
    }

    private function getModelData(?string $userId = null): array
    {
        $query = VirtualModel::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId));
        
        return [
            'items' => $query->where('status', '!=', 'failed')->latest()->get(),
            'count' => $query->where('status', 'completed')->count(),
        ];
    }

    public function deleteGeneration(Request $request, $type, $id)
    {
        try {
            DB::beginTransaction();

            $item = $this->findGenerationItem($type, $id);

            // Verificación de permisos, aunque ya no es redundante.
            if ($request->has('user_id') && $item->user_id != $request->user_id) {
                throw new \Exception('No tienes permisos para eliminar esta generación');
            }

            // El modelo se encarga de eliminar los archivos automáticamente.
            $item->delete();

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

    public function deleteAllGenerations(Request $request)
    {
        try {
            DB::beginTransaction();

            $type = $request->input('type');
            $userId = $request->input('user_id');

            if ($type) {
                $this->deleteGenerationsByType($type, $userId);
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

    private function findGenerationItem(string $type, int $id)
    {
        return match($type) {
            'video' => ImageToVideo::findOrFail($id),
            'tryon' => VirtualTryOn::findOrFail($id),
            'model' => VirtualModel::findOrFail($id),
            default => throw new \Exception('Tipo de generación no válido'),
        };
    }

    private function deleteGenerationsByType(string $type, ?string $userId = null): void
    {
        $query = match($type) {
            'video' => ImageToVideo::query(),
            'tryon' => VirtualTryOn::query(),
            'model' => VirtualModel::query(),
            default => throw new \Exception('Tipo de generación no válido'),
        };

        if ($userId) {
            $query->where('user_id', $userId);
        }

        // Iterar y eliminar para que los modelos se encarguen de los archivos
        $items = $query->get();
        foreach ($items as $item) {
            $item->delete();
        }
    }

    private function deleteAllTypes(?string $userId = null): void
    {
        $this->deleteGenerationsByType('video', $userId);
        $this->deleteGenerationsByType('tryon', $userId);
        $this->deleteGenerationsByType('model', $userId);
    }
}