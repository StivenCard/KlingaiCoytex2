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
        try {
            $apiConsumption = $this->klingService->getApiConsumption();
            $resourcePacks = $this->processApiConsumption($apiConsumption['data']['resource_pack_subscribe_infos'] ?? []);
        } catch (\Exception $e) {
            Log::error("Error obteniendo consumo de API: " . $e->getMessage());
            $resourcePacks = [];
        }

        $filters = $this->processFilters($request);

        $data = [
            'videos' => $this->getData(ImageToVideo::class, $filters['video_user_id'] ?? null),
            'tryons' => $this->getData(VirtualTryOn::class, $filters['tryon_user_id'] ?? null),
            'models' => $this->getData(VirtualModel::class, $filters['model_user_id'] ?? null),
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

    private function getData(string $modelClass, ?int $userId = null): array
    {
        $query = $modelClass::query()
            ->with('user')
            ->when($userId, fn($q) => $q->where('user_id', $userId));

        return [
            'items' => (clone $query)->where('status', '!=', 'failed')->latest()->get(),
            'count' => (clone $query)->where('status', 'completed')->count(),
        ];
    }

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

            if ($request->has('user_id') && $item->user_id != $request->user_id) {
                throw new \Exception('No tienes permisos para eliminar esta generación');
            }

            // Ahora el borrado de archivos se maneja desde el modelo
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
            $query->where('user_id', $userId);
        }

        $query->each(function ($item) {
            $item->delete(); // El modelo maneja sus archivos
        });
    }

    private function deleteAllTypes(?int $userId = null): void
    {
        $this->deleteGenerationsByType('video', $userId);
        $this->deleteGenerationsByType('tryon', $userId);
        $this->deleteGenerationsByType('model', $userId);
    }
}
