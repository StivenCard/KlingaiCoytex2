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
use App\Models\OmniModel;
use Carbon\Carbon;

/**
 * Class AdminGenerationsController
 *
 * Controlador administrativo para gestionar las generaciones de la plataforma:
 * - Videos
 * - Try-Ons
 * - Modelos virtuales
 *
 * Permite listar, filtrar, consultar estadísticas y eliminar generaciones.
 */
class AdminGenerationsController extends Controller
{
    /**
     * Servicio para interactuar con la API de KlingAI.
     *
     * @var KlingApiService
     */
    private $klingService;
    /**
     * Tipos de modelo soportados.
     */
    private const MODEL_TYPES = ['video', 'tryon', 'model', 'omni'];
    /**
     * Mapeo de tipo de modelo a clase Eloquent correspondiente.
     */
    private const MODEL_CLASSES = [
        'video' => ImageToVideo::class,
        'tryon' => VirtualTryOn::class,
        'model' => VirtualModel::class,
        'omni'  => OmniModel::class,
    ];

    private const SECTION_MAP = [
        'videos' => 'video',
        'tryons' => 'tryon',
        'models' => 'model',
        'omnis'  => 'omni',
    ];

    /**
     * Constructor con inyección de dependencias.
     *
     * @param KlingApiService $klingService
     */
    public function __construct(KlingApiService $klingService)
    {
        $this->klingService = $klingService;
    }

    /**
     * Vista principal de generaciones administrativas.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 1. Determinar la sección activa
        $activeSection = $request->input('section', 'all');

        // 2. Obtener datos de consumo de API
        $resourcePacks = $this->getResourcePacks();

        // 3. Procesar filtros
        $filters = $this->processFilters($request, $activeSection);

        // 4. Obtener datos según la sección activa
        $data = $this->getSectionData($filters, $activeSection);

        // 5. Verificar si no hay resultados para filtro global
        $noResultsForGlobalUser = $this->checkNoResultsForGlobalUser($data, $filters);

        // 6. Calcular totales generales
        $totals = $this->calculateTotals();

        // 7. Obtener reglas de precios
        $pricingRules = $this->getPricingRules();

        // 8. Calcular estadísticas de filtro global
        $globalFilterStats = $this->calculateGlobalFilterStats($data, $filters);

        $resourcePacks = $this->getResourcePacks($request->boolean('refresh'));

        return view('admin.generations', compact(
            'data',
            'totals',
            'resourcePacks',
            'filters',
            'activeSection',
            'pricingRules',
            'globalFilterStats',
            'noResultsForGlobalUser'
        ));
    }

    /**
     * Obtiene el consumo de API y procesa los paquetes de recursos.
     *
     * @return array
     */
    private function getResourcePacks(bool $forceRefresh = false): array
    {
        try {
            $apiConsumption = $this->klingService->getApiConsumption(forceRefresh: $forceRefresh);
            $resourcePacks  = $this->processApiConsumption(
                $apiConsumption['data']['resource_pack_subscribe_infos'] ?? []
            );

            if (empty($resourcePacks)) {
                session()->flash('info_message', 'No hay paquetes de recursos activos o disponibles en su cuenta.');
            }

            return $resourcePacks;
        } catch (\Exception $e) {
            Log::error('Error obteniendo consumo de API: ' . $e->getMessage());
            session()->flash('api_error', 'Error de la API: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Procesa filtros globales y específicos por sección.
     *
     * @param Request $request
     * @param string $activeSection
     * @return array
     */
    private function processFilters(Request $request, string $activeSection): array
    {
        $filters = [
            'active_section' => $activeSection,
            'global_user_id' => $request->input('global_user_id'),
        ];

        if ($activeSection === 'all') {
            // Para sección "all", incluir todos los filtros individuales
            foreach (self::SECTION_MAP as $sectionKey => $filterKey) {
                $filters["{$filterKey}_user_id"] = $request->input("{$filterKey}_user_id");
            }
        } else {
            // Para secciones individuales, usar la clave correcta\
            $filterKey = self::SECTION_MAP[$activeSection] ?? $activeSection;
            $filters["{$filterKey}_user_id"] = $request->input("{$filterKey}_user_id");
        }

        return $filters;
    }

    /**
     * Obtiene datos de generaciones según sección activa.
     *
     * @param array $filters
     * @param string $activeSection
     * @return array
     */
    private function getSectionData(array $filters, string $activeSection): array
    {
        $globalUserId = $filters['global_user_id'] ?? null;
        $data = [];

        if ($activeSection === 'all') {
            foreach (self::SECTION_MAP as $sectionKey => $type) {
                $userIdFilter = $filters["{$type}_user_id"] ?? null;
                $data[$sectionKey] = $this->getModelData($type, $userIdFilter, $globalUserId);
            }
        } else {
            $type = self::SECTION_MAP[$activeSection] ?? rtrim($activeSection, 's');
            $userIdFilter = $filters["{$type}_user_id"] ?? null;
            $data[$activeSection] = $this->getModelData($type, $userIdFilter, $globalUserId);
        }

        return $data;
    }

    /**
     * Consulta y resume datos de un modelo específico.
     *
     * @param string $type
     * @param string|null $userId
     * @param string|null $globalUserId
     * @return array{items: \Illuminate\Contracts\Pagination\Paginator, count:int, tokens:int, price:float}
     */
    private function getModelData(string $type, ?string $userId = null, ?string $globalUserId = null): array
    {
        $modelClass = self::MODEL_CLASSES[$type] ?? null;

        if (!$modelClass) {
            return ['items' => collect(), 'count' => 0, 'tokens' => 0, 'price' => 0];
        }

        $query = $modelClass::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when($globalUserId, fn($q) => $q->where('user_id', $globalUserId));

        $itemsQuery = (clone $query)
            ->where('status', '!=', 'failed')
            ->latest();

        $countQuery = (clone $query)
            ->where('status', 'completed');

        $pageName = "{$type}s_page";

        return [
            'items'  => $itemsQuery->paginate(10, ['*'], $pageName),
            'count'  => $countQuery->count(),
            'tokens' => $query->sum('tokens'),
            'price'  => $query->sum('price'),
        ];
    }

    /**
     * Verifica si no hay resultados para un filtro global de usuario.
     *
     * @param array $data
     * @param array $filters
     * @return bool
     */
    private function checkNoResultsForGlobalUser(array $data, array $filters): bool
    {
        if (!$filters['global_user_id']) {
            return false;
        }

        $totalCount = 0;
        foreach ($data as $sectionData) {
            $totalCount += $sectionData['count'] ?? 0;
        }

        return $totalCount === 0;
    }

    /**
     * Calcula los totales generales y por tipo.
     *
     * @return array
     */
    private function calculateTotals(): array
    {
        $totals = ['all' => ['count' => 0, 'tokens' => 0, 'price' => 0]];

        foreach (self::MODEL_TYPES as $type) {
            $modelClass = self::MODEL_CLASSES[$type];
            $totals[$type . 's'] = [
                'count'  => $modelClass::where('status', 'completed')->count(),
                'tokens' => $modelClass::sum('tokens'),
                'price'  => $modelClass::sum('price'),
            ];

            // Sumar al total general
            $totals['all']['count'] += $totals[$type . 's']['count'];
            $totals['all']['tokens'] += $totals[$type . 's']['tokens'];
            $totals['all']['price'] += $totals[$type . 's']['price'];
        }

        return $totals;
    }

    /**
     * Obtiene las reglas de precios disponibles.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getPricingRules()
    {
        return PricingRule::query()
            ->orderBy('model_name')
            ->orderBy('feature')
            ->orderBy('resolution')
            ->get();
    }

    /**
     * Calcula estadísticas cuando se aplica un filtro global de usuario.
     *
     * @param array $data
     * @param array $filters
     * @return array|null
     */
    private function calculateGlobalFilterStats(array $data, array $filters): ?array
    {
        if (!$filters['global_user_id']) {
            return null;
        }

        $stats = ['count' => 0, 'tokens' => 0, 'price' => 0];

        foreach ($data as $sectionData) {
            $stats['count'] += $sectionData['count'] ?? 0;
            $stats['tokens'] += $sectionData['tokens'] ?? 0;
            $stats['price'] += $sectionData['price'] ?? 0;
        }

        return $stats;
    }

    /**
     * Procesa los paquetes de recursos y calcula métricas de uso.
     *
     * @param array $packs
     * @return array
     */
    private function processApiConsumption(array $packs): array
    {
        $tz  = config('app.timezone');
        $now = Carbon::now($tz);

        $statusMap = [
            'online'     => ['label' => 'En uso',       'class' => 'success'],
            'toBeOnline' => ['label' => 'Pendiente',    'class' => 'info'],
            'expired'    => ['label' => 'Expirado',     'class' => 'danger'],
            'runOut'     => ['label' => 'Agotado',      'class' => 'dark'],
        ];

        return collect($packs)->map(function ($pack) use ($now, $tz, $statusMap) {
            $total     = (float) ($pack['total_quantity'] ?? 0);
            $remaining = (float) ($pack['remaining_quantity'] ?? 0);
            $used      = max($total - $remaining, 0);

            $purchase   = Carbon::createFromTimestampMs($pack['purchase_time'] ?? 0, $tz);
            $effective  = Carbon::createFromTimestampMs($pack['effective_time'] ?? 0, $tz);
            $expiration = Carbon::createFromTimestampMs($pack['invalid_time'] ?? 0, $tz);

            // Días activos reales: desde que se activó hasta hoy (o hasta que expiró)
            $to         = $now->lt($expiration) ? $now : $expiration;
            $daysActive = max(1, (int) ceil(abs($to->diffInHours($effective)) / 24));

            $status = $pack['status'] ?? 'unknown';

            return $pack + [
                'used_quantity'   => $used,
                'percentage_used' => $total > 0 ? min(($used / $total) * 100, 100) : 0,
                'daily_usage'     => $effective->isFuture() ? 0 : $used / $daysActive,
                'status_label'    => $statusMap[$status]['label'] ?? ucfirst($status),
                'status_class'    => $statusMap[$status]['class'] ?? 'warning',
                'is_periodic'     => ($pack['resource_pack_type'] ?? '') === 'constant_period',
                'formatted_dates' => [
                    'purchase'   => $purchase->format('d/m/Y H:i:s'),
                    'effective'  => $effective->format('d/m/Y H:i:s'),
                    'expiration' => $expiration->format('d/m/Y H:i:s'),
                ],
            ];
        })
            // Primero los activos
            ->sortBy(fn($p) => $p['status'] === 'online' ? 0 : 1)
            ->values()
            ->all();
    }

    /**
     * Elimina una generación individual.
     *
     * @param Request $request
     * @param string $type
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Elimina todas las generaciones (global o por usuario).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
                'user_id' => $userId,
                'reload' => !$userId
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
     * Busca un ítem de generación por tipo e ID.
     *
     * @param string $type
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Exception
     */
    private function findGenerationItem(string $type, int $id)
    {
        $modelClass = self::MODEL_CLASSES[$type] ?? null;

        if (!$modelClass) {
            throw new \Exception('Tipo de generación no válido');
        }

        return $modelClass::findOrFail($id);
    }

    /**
     * Elimina todas las generaciones de un tipo específico.
     *
     * @param string $type
     * @param string|null $userId
     * @return void
     */
    private function deleteGenerationsByType(string $type, ?string $userId = null): void
    {
        $modelClass = self::MODEL_CLASSES[$type] ?? null;

        if (!$modelClass) {
            throw new \Exception('Tipo de generación no válido');
        }

        $query = $modelClass::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $items = $query->get();
        foreach ($items as $item) {
            $item->delete();
        }
    }

    /**
     * Elimina todas las generaciones de todos los tipos.
     *
     * @param string|null $userId
     * @return void
     */
    private function deleteAllTypes(?string $userId = null): void
    {
        foreach (self::MODEL_TYPES as $type) {
            $this->deleteGenerationsByType($type, $userId);
        }
    }
}
