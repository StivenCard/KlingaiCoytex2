<?php

namespace App\Http\Controllers;

use App\Models\ApiLog;
use App\Models\ImageToVideo;
use App\Models\VirtualModel;
use App\Models\VirtualTryOn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\KlingAi\KlingApiService;
use Illuminate\Database\Eloquent\Builder;

class AdminGenerationsController extends Controller
{
    private $klingService;

    public function __construct(KlingApiService $klingService)
    {
        $this->klingService = $klingService;
    }

    /**
     * Muestra la vista de generaciones con filtros por usuario o task_id.
     */
    public function index(Request $request)
    {
        try {
            $apiConsumption = $this->klingService->getApiConsumption();
            $resourcePacks = $this->processApiConsumption($apiConsumption['data']['resource_pack_subscribe_infos'] ?? []);
        } catch (\Exception $e) {
            Log::error("Error obteniendo consumo de API: " . $e->getMessage());
            $resourcePacks = [];
        }

        // Procesa los filtros de la solicitud para obtener un user_id unificado
        $filters = $this->processFilters($request);
        $filteredUserId = $filters['user_id'] ?? null;
        $filteredTaskId = $filters['task_id'] ?? null;

        $data = [
            'videos' => $this->getData(ImageToVideo::class, 'multi_image_to_video', $filteredUserId),
            'tryons' => $this->getData(VirtualTryOn::class, 'virtual_try_on', $filteredUserId),
            'models' => $this->getData(VirtualModel::class, 'virtual_model', $filteredUserId),
        ];

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
     * Procesa los filtros de la solicitud, priorizando task_id sobre user_id.
     * @param Request $request
     * @return array
     */
    private function processFilters(Request $request): array
    {
        $filters = [
            'task_id' => $request->input('task_id'),
            'user_id' => $request->input('user_id'),
        ];

        // Si se proporciona un task_id, buscar el user_id asociado y anular el user_id del request
        if ($filters['task_id']) {
            $apiLog = ApiLog::where('task_id', $filters['task_id'])->first();
            if ($apiLog) {
                $filters['user_id'] = $apiLog->user_id;
            } else {
                // Si el task_id no existe, no se filtra por usuario
                $filters['user_id'] = null;
            }
        }

        return $filters;
    }

    /**
     * Obtiene los datos de una tabla de generación, opcionalmente filtrando por user_id.
     */
    private function getData(string $modelClass, string $operationType, ?string $userId = null): array
    {
        $tableName = (new $modelClass())->getTable();
        $query = $modelClass::query();

        if ($userId) {
            $query->whereIn('task_id', function ($subQuery) use ($userId, $operationType) {
                $subQuery->select('task_id')
                    ->from('api_logs')
                    ->where('user_id', $userId)
                    ->where('operation_type', $operationType);
            });
        }
        
        return [
            'items' => (clone $query)->where('status', '!=', 'failed')->latest()->get(),
            'count' => (clone $query)->where('status', 'completed')->count(),
        ];
    }
    
    // El resto de tus métodos (findGenerationItem, deleteGeneration, etc.) no necesitan ser modificados
    // ya que la lógica de filtrado se maneja en los métodos index y getData.
    
    // ... Tus otros métodos ...
    
    private function findGenerationItem(string $type, int $id)
    {
        return match ($type) {
            'video' => ImageToVideo::findOrFail($id),
            'tryon' => VirtualTryOn::findOrFail($id),
            'model' => VirtualModel::findOrFail($id),
            default => throw new \Exception('Tipo de generación no válido'),
        };
    }
    
    public function deleteGeneration(Request $request, $type, $id)
    {
        try {
            DB::beginTransaction();
    
            $item = $this->findGenerationItem($type, $id);
    
            $apiLog = ApiLog::where('task_id', $item->task_id)->first();
            $itemUserId = $apiLog->user_id ?? null;
    
            if ($request->has('user_id') && $itemUserId != $request->user_id) {
                throw new \Exception('No tienes permisos para eliminar esta generación');
            }
    
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
                'error' => $e->getMessage()
            ]);
    
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
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
    
    private function deleteGenerationsByType(string $type, ?int $userId = null): void
    {
        $query = match ($type) {
            'video' => ImageToVideo::query(),
            'tryon' => VirtualTryOn::query(),
            'model' => VirtualModel::query(),
            default => throw new \Exception('Tipo de generación no válido'),
        };
    
        if ($userId) {
            $tableName = $query->getModel()->getTable();
            $query->whereIn('task_id', function ($subQuery) use ($userId, $tableName) {
                $subQuery->select('task_id')
                    ->from('api_logs')
                    ->where('user_id', $userId)
                    ->where('operation_type', $this->getOperationType($tableName));
            });
        }
    
        $query->each(function ($item) {
            $item->delete();
        });
    }
    
    private function getOperationType(string $tableName): string
    {
        return match ($tableName) {
            'image_to_videos' => 'multi_image_to_video',
            'virtual_try_ons' => 'virtual_try_on',
            'virtual_models' => 'virtual_model',
            default => throw new \Exception("Tipo de tabla no reconocido: $tableName"),
        };
    }
    
    private function deleteAllTypes(?int $userId = null): void
    {
        $this->deleteGenerationsByType('video', $userId);
        $this->deleteGenerationsByType('tryon', $userId);
        $this->deleteGenerationsByType('model', $userId);
    }
}