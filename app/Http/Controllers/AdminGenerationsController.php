<?php

namespace App\Http\Controllers;

use App\Models\ImageToVideo;
use App\Models\VirtualModel;
use App\Models\VirtualTryOn;
use App\Models\PricingRule;
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
        // 1. Determinar la sección activa
        $activeSection = $request->input('section', 'all'); // 'all', 'videos', 'tryons', 'models'
        
        // 2. Obtener datos de consumo de API
        try {
            $apiConsumption = $this->klingService->getApiConsumption();
            $resourcePacks = $this->processApiConsumption($apiConsumption['data']['resource_pack_subscribe_infos'] ?? []);

            if (empty($resourcePacks)) {
                session()->flash('info_message', 'No hay paquetes de recursos activos o disponibles en su cuenta.');
            }
        } catch (\Exception $e) {
            Log::error("Error obteniendo consumo de API: " . $e->getMessage());
            $resourcePacks = [];
            session()->flash('api_error', 'Error de la API: ' . $e->getMessage());
        }

        // 3. Procesar filtros específicos por sección
        $filters = $this->processFilters($request, $activeSection);

        // 4. Obtener datos según la sección activa
        $data = $this->getData($filters, $activeSection);

        $noResultsForGlobalUser = false;
        if ($filters['global_user_id']) {
            $noResultsForGlobalUser = 
                ($data['videos']['count'] === 0) &&
                ($data['tryons']['count'] === 0) &&
                ($data['models']['count'] === 0);
        }

        // 5. Calcular totales generales (siempre sin filtros para el resumen)
        $videoData = $this->getVideoData();
        $tryOnData = $this->getTryOnData();
        $modelData = $this->getModelData();

        // 6. Cargar reglas de precios
            $pricingMap = [
            ['model' => 'kling-v1-6', 'duration' => 5,  'mode' => 'std'],
            ['model' => 'kling-v1-6', 'duration' => 10, 'mode' => 'std'],
            ['model' => 'kling-v1-6', 'duration' => 5,  'mode' => 'pro'],
            ['model' => 'kling-v1-6', 'duration' => 10, 'mode' => 'pro'],
            ['model' => 'kolors-virtual-try-on-v1-5',  'duration' => null, 'mode' => 'default'],
            ['model' => 'kolors-v1-5',   'duration' => null, 'mode' => 'text-to-image'],
        ];

        //7. Filtrado Global
        if ($filters['global_user_id']) {
            $globalFilterStats = [
                'count'  => $data['videos']['count'] + $data['tryons']['count'] + $data['models']['count'],
                'tokens' => $data['videos']['tokens'] + $data['tryons']['tokens'] + $data['models']['tokens'],
                'price'  => $data['videos']['price'] + $data['tryons']['price'] + $data['models']['price'],
            ];
        } else {
            $globalFilterStats = null;
        }

        // Buscar solo esos registros
        $pricingRules = collect($pricingMap)->map(function ($map) {
            return PricingRule::where('model_name', $map['model'])
                ->when($map['duration'], fn($q) => $q->where('duration', $map['duration']))
                ->when($map['mode'], fn($q) => $q->where('mode', $map['mode']))
                ->first();
        })->filter(); // quitamos nulls por si algo no existe

        $totals = [
            'videos' => [
                'count'  => $videoData['count'],
                'tokens' => $videoData['tokens'],
                'price'  => $videoData['price'],
            ],
            'tryons' => [
                'count'  => $tryOnData['count'],
                'tokens' => $tryOnData['tokens'],
                'price'  => $tryOnData['price'],
            ],
            'models' => [
                'count'  => $modelData['count'],
                'tokens' => $modelData['tokens'],
                'price'  => $modelData['price'],
            ],
            'all' => [
                'count'  => $videoData['count'] + $tryOnData['count'] + $modelData['count'],
                'tokens' => $videoData['tokens'] + $tryOnData['tokens'] + $modelData['tokens'],
                'price'  => $videoData['price'] + $tryOnData['price'] + $modelData['price'],
            ],
        ];
        return view('admin.generations', compact('data', 'totals', 'resourcePacks', 'filters', 'activeSection','pricingRules','globalFilterStats','noResultsForGlobalUser'));
    }

    private function processFilters(Request $request, string $activeSection): array
    {
        $filters = [
            'active_section' => $activeSection,
            // Filtro global siempre presente
            'global_user_id' => $request->input('global_user_id'),
        ];

        // Filtros específicos por sección
        switch ($activeSection) {
            case 'videos':
                $filters['video_user_id'] = $request->input('video_user_id');
                break;
            case 'tryons':
                $filters['tryon_user_id'] = $request->input('tryon_user_id');
                break;
            case 'models':
                $filters['model_user_id'] = $request->input('model_user_id');
                break;
            case 'all':
            default:
                $filters['video_user_id'] = $request->input('video_user_id');
                $filters['tryon_user_id'] = $request->input('tryon_user_id');
                $filters['model_user_id'] = $request->input('model_user_id');
                break;
        }

        return $filters;
    }


    private function getData(array $filters, string $activeSection): array
    {
        $data = [];
        $globalUserId = $filters['global_user_id'] ?? null;

        switch ($activeSection) {
            case 'videos':
                $data['videos'] = $this->getVideoData($filters['video_user_id'] ?? null, $globalUserId);
                break;
            case 'tryons':
                $data['tryons'] = $this->getTryOnData($filters['tryon_user_id'] ?? null, $globalUserId);
                break;
            case 'models':
                $data['models'] = $this->getModelData($filters['model_user_id'] ?? null, $globalUserId);
                break;
            case 'all':
            default:
                $data = [
                    'videos' => $this->getVideoData(null, $globalUserId),
                    'tryons' => $this->getTryOnData(null, $globalUserId),
                    'models' => $this->getModelData(null, $globalUserId),
                ];
                break;
        }

        return $data;
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

    private function getVideoData(?string $userId = null, ?string $globalUserId = null): array
    {
        // Query base con filtros
        $baseQuery = ImageToVideo::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when($globalUserId, fn($q) => $q->where('user_id', $globalUserId));

        // Paginación de items (solo los que no fallaron)
        $itemsQuery = ImageToVideo::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when($globalUserId, fn($q) => $q->where('user_id', $globalUserId))
            ->where('status', '!=', 'failed')
            ->latest();

        // Conteo solo de completados
        $countQuery = ImageToVideo::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when($globalUserId, fn($q) => $q->where('user_id', $globalUserId))
            ->where('status', 'completed');

        // Tokens (suma general)
        $tokensQuery = ImageToVideo::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when($globalUserId, fn($q) => $q->where('user_id', $globalUserId));

        // Precio (suma general)
        $priceQuery = ImageToVideo::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when($globalUserId, fn($q) => $q->where('user_id', $globalUserId));

        return [
            'items'  => $itemsQuery->paginate(3, ['*'], 'videos_page'), // 🔹 Paginación aquí
            'count'  => $countQuery->count(),
            'tokens' => $tokensQuery->sum('tokens'),
            'price'  => $priceQuery->sum('price'),
        ];
    }


    private function getTryOnData(?string $userId = null, ?string $globalUserId = null): array
    {
        // Items paginados (excepto los fallidos)
        $itemsQuery = VirtualTryOn::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when($globalUserId, fn($q) => $q->where('user_id', $globalUserId))
            ->where('status', '!=', 'failed')
            ->latest();

        // Conteo de completados
        $countQuery = VirtualTryOn::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when($globalUserId, fn($q) => $q->where('user_id', $globalUserId))
            ->where('status', 'completed');

        // Tokens
        $tokensQuery = VirtualTryOn::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when($globalUserId, fn($q) => $q->where('user_id', $globalUserId));

        // Precio
        $priceQuery = VirtualTryOn::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when($globalUserId, fn($q) => $q->where('user_id', $globalUserId));

        return [
            'items'  => $itemsQuery->paginate(3, ['*'], 'tryons_page'), // 🔹 Paginación
            'count'  => $countQuery->count(),
            'tokens' => $tokensQuery->sum('tokens'),
            'price'  => $priceQuery->sum('price'),
        ];
    }

    private function getModelData(?string $userId = null, ?string $globalUserId = null): array
    {
        // Items paginados (excepto los fallidos)
        $itemsQuery = VirtualModel::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when($globalUserId, fn($q) => $q->where('user_id', $globalUserId))
            ->where('status', '!=', 'failed')
            ->latest();

        // Conteo de completados
        $countQuery = VirtualModel::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when($globalUserId, fn($q) => $q->where('user_id', $globalUserId))
            ->where('status', 'completed');

        // Tokens
        $tokensQuery = VirtualModel::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when($globalUserId, fn($q) => $q->where('user_id', $globalUserId));

        // Precio
        $priceQuery = VirtualModel::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when($globalUserId, fn($q) => $q->where('user_id', $globalUserId));

        return [
            'items'  => $itemsQuery->paginate(3, ['*'], 'models_page'), // 🔹 Paginación
            'count'  => $countQuery->count(),
            'tokens' => $tokensQuery->sum('tokens'),
            'price'  => $priceQuery->sum('price'),
        ];
    }

    public function deleteGeneration(Request $request, $type, $id)
    {
        try {
            DB::beginTransaction();

            $item = $this->findGenerationItem($type, $id);

            if ($request->has('user_id') && $item->user_id != $request->user_id) {
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
                'message' => $message,
                'user_id' => $userId, // Indicamos si se estaba filtrando por usuario
                'reload' => !$userId // Solo recargamos completamente si no había filtro de usuario
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