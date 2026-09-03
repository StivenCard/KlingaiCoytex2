@extends('layouts.probador-virtual-ia')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="mt-3">
        <div class="main-container d-flex flex-wrap pt-3">
            {{-- Bloque de Resumen --}}
            <div class="mb-4 col-12">
                <div class="">
                    <div class="row">
                        {{-- Card: Videos --}}
                        <div class="col-12 col-md-3 mb-3">
                            <a href="{{ route('admin.generations', ['section' => 'videos']) }}"
                                class="text-decoration-none d-block card-link {{ $activeSection === 'videos' ? 'active' : '' }}">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Videos</h5>
                                        <h2>{{ $totals['videos']['count'] }}</h2>
                                        <small>{{ $totals['videos']['tokens'] }} tokens |
                                            ${{ number_format($totals['videos']['price'], 2) }}</small>
                                    </div>
                                </div>
                            </a>
                        </div>

                        {{-- Card: Try-Ons --}}
                        <div class="col-12 col-md-3 mb-3">
                            <a href="{{ route('admin.generations', ['section' => 'tryons']) }}"
                                class="text-decoration-none d-block card-link {{ $activeSection === 'tryons' ? 'active' : '' }}">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Try-Ons</h5>
                                        <h2>{{ $totals['tryons']['count'] }}</h2>
                                        <small>{{ $totals['tryons']['tokens'] }} tokens |
                                            ${{ number_format($totals['tryons']['price'], 2) }}</small>
                                    </div>
                                </div>
                            </a>
                        </div>

                        {{-- Card: Modelos --}}
                        <div class="col-12 col-md-3 mb-3">
                            <a href="{{ route('admin.generations', ['section' => 'models']) }}"
                                class="text-decoration-none d-block card-link {{ $activeSection === 'models' ? 'active' : '' }}">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Modelos</h5>
                                        <h2>{{ $totals['models']['count'] }}</h2>
                                        <small>{{ $totals['models']['tokens'] }} tokens |
                                            ${{ number_format($totals['models']['price'], 2) }}</small>
                                    </div>
                                </div>
                            </a>
                        </div>

                        {{-- Card: Total en conjunto --}}
                        <div class="col-12 col-md-3 mb-3">
                            <a href="{{ route('admin.generations', ['section' => 'all']) }}"
                                class="text-decoration-none d-block card-link {{ $activeSection === 'all' ? 'active' : '' }}">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Total en conjunto</h5>
                                        <h2>{{ $totals['all']['count'] ?? 0 }}</h2>
                                        <small>{{ $totals['all']['tokens'] ?? 0 }} tokens |
                                            ${{ number_format($totals['all']['price'] ?? 0, 2) }}</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna izquierda (se mantiene igual) -->
            <div class="col-12 col-lg-4 d-flex flex-column overflow-hidden px-2">
                <!-- Consumo de API (se mantiene igual) -->
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-tachometer-alt"></i> Consumo de API
                            <button class="btn btn-sm btn-light float-right" onclick="window.location.reload()">
                                <i class="fas fa-sync"></i>
                            </button>
                        </h5>
                    </div>
                    <div class="card-body">
                        @if (empty($resourcePacks))
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                No se pudo obtener información de consumo de la API
                            </div>
                        @else
                            @foreach ($resourcePacks as $pack)
                                <div class="card mb-3 shadow-sm">
                                    <div class="card-body">
                                        <h6 class="card-title d-flex justify-content-between align-items-center">
                                            <span>
                                                <i class="fas fa-box"></i> {{ $pack['resource_pack_name'] }}
                                            </span>
                                            <span
                                                class="badge badge-{{ $pack['status'] === 'online' ? 'success' : ($pack['status'] === 'expired' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($pack['status']) }}
                                            </span>
                                        </h6>

                                        <div class="progress mb-2" style="height: 20px;"
                                            title="Consumido: {{ number_format($pack['used_quantity'], 2) }} tokens">
                                            <div class="progress-bar {{ $pack['percentage_used'] > 80 ? 'bg-danger' : 'bg-success' }}"
                                                role="progressbar" style="width: {{ $pack['percentage_used'] }}%">
                                                {{ number_format($pack['percentage_used'], 1) }}%
                                            </div>
                                        </div>

                                        <div class="row text-center mb-3">
                                            <div class="col-4">
                                                <small class="text-muted d-block">Restante</small>
                                                <strong class="text-success">
                                                    {{ number_format($pack['remaining_quantity'], 2) }}
                                                </strong>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted d-block">Total</small>
                                                <strong>
                                                    {{ number_format($pack['total_quantity'], 2) }}
                                                </strong>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted d-block">Consumo/día</small>
                                                <strong class="text-info">
                                                    {{ number_format($pack['daily_usage'], 2) }}
                                                </strong>
                                            </div>
                                        </div>
                                        <div class="row text-center mb-3">
                                            <div class="col-4">
                                                <small class="text-muted d-block">Consumido</small>
                                                <strong class="text-warning">
                                                    {{ number_format($pack['used_quantity'], 2) }}
                                                </strong>
                                            </div>
                                        </div>

                                        <div class="small">
                                            <div class="mb-1">
                                                <i class="fas fa-shopping-cart text-success"></i>
                                                <strong>Comprado:</strong>
                                                <span class="text-muted">{{ $pack['formatted_dates']['purchase'] }}</span>
                                            </div>
                                            <div class="mb-1">
                                                <i class="fas fa-play-circle text-primary"></i>
                                                <strong>Activado:</strong>
                                                <span class="text-muted">{{ $pack['formatted_dates']['effective'] }}</span>
                                            </div>
                                            <div class="mb-1">
                                                <i class="fas fa-hourglass-end text-danger"></i>
                                                <strong>Expira:</strong>
                                                <span
                                                    class="text-muted">{{ $pack['formatted_dates']['expiration'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <!-- Costos por Operación -->
                        {{-- <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Costos por Operación</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                @foreach ($pricingRules as $rule)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            @if (Str::contains($rule->model_name, 'kling'))
                                                <i class="fas fa-film"></i> Video ({{ $rule->duration }}s) - {{ ucfirst($rule->mode) }}
                                            @elseif(Str::contains($rule->model_name, 'try-on'))
                                                <i class="fas fa-tshirt"></i> Try-on
                                            @elseif(Str::contains($rule->model_name, 'kolors'))
                                                <i class="fas fa-image"></i> Modelo Virtual
                                            @endif

                                            <small class="d-block text-muted">{{ $rule->tokens }} tokens</small>
                                        </div>

                                        <span class="badge
                                            @if (Str::contains($rule->model_name, 'kling')) badge-primary
                                            @elseif(Str::contains($rule->model_name, 'try-on')) badge-success
                                            @elseif(Str::contains($rule->model_name, 'kolors')) badge-info
                                            @else badge-secondary
                                            @endif
                                        ">
                                            ${{$rule->price }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div> --}}
                    {{-- Costos actuales de Kling AI --}}
                    <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-dollar-sign"></i> Costos actuales de Kling AI
                                </h6>
                            </div>
                            {{-- Contenedor con scroll interno --}}
                            <div class="card-body p-2"
                                style="
                                max-height: 450px;
                                overflow-y: auto;
                                overflow-x: hidden;
                            ">
                                @if ($pricingRules->isEmpty())
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        No hay reglas de precios configuradas.
                                    </div>
                                @else
                                    @php
                                        $groupedPricing = $pricingRules->groupBy('model_name');
                                    @endphp
                                    @foreach ($groupedPricing as $modelName => $modelRules)
                                        <div class="card mb-3 shadow-sm">
                                            {{-- Modelo --}}
                                            <div class="card-header bg-dark text-white py-2">
                                                <strong>
                                                    <i class="fas fa-robot"></i>
                                                    {{ $modelName }}
                                                </strong>
                                            </div>
                                            <div class="card-body p-2">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered mb-0">
                                                        <thead class="thead-light">
                                                            <tr>
                                                                <th>Característica</th>
                                                                <th>Resolución</th>
                                                                <th>Facturación</th>
                                                                <th>Tokens</th>
                                                                <th>Precio</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($modelRules as $rule)
                                                                <tr>
                                                                    {{-- Característica --}}
                                                                    <td>
                                                                        @if ($rule->feature)
                                                                            {{ ucwords(str_replace('_', ' ', $rule->feature)) }}
                                                                        @else
                                                                            <span class="text-muted">
                                                                                Predeterminado
                                                                            </span>
                                                                        @endif
                                                                    </td>
                                                                    {{-- Resolución --}}
                                                                    <td>
                                                                        @if ($rule->resolution)
                                                                            <span class="badge badge-info">
                                                                                {{ strtoupper($rule->resolution) }}
                                                                            </span>
                                                                        @else
                                                                            <span class="text-muted">
                                                                                —
                                                                            </span>
                                                                        @endif
                                                                    </td>
                                                                    {{-- Facturación --}}
                                                                    <td>
                                                                        @if ($rule->billing_type)
                                                                            <span class="badge badge-secondary">
                                                                                {{ ucwords(str_replace('_', ' ', $rule->billing_type)) }}
                                                                            </span>
                                                                        @else
                                                                            <span class="text-muted">
                                                                                —
                                                                            </span>
                                                                        @endif
                                                                    </td>
                                                                    {{-- Tokens --}}
                                                                    <td>
                                                                        <span class="badge badge-primary">
                                                                            {{ rtrim(rtrim(number_format($rule->tokens, 3, '.', ''), '0'), '.') }}
                                                                        </span>
                                                                    </td>
                                                                    {{-- Precio --}}
                                                                    <td>
                                                                        <strong class="text-success">
                                                                            ${{ number_format($rule->price, 3) }}
                                                                        </strong>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="alert alert-secondary mt-3 mb-0 small">
                            <i class="fas fa-info-circle"></i>
                            <strong>Nota:</strong>
                            Los precios mostrados corresponden a las reglas actualmente configuradas
                            en Kling AI. Los precios y modelos anteriores se conservan únicamente
                            como referencia histórica en la documentación de mantenimiento.
                        </div>
                        @if ($totals['all']['count'] > 0)
                            <div class="mt-4 ">
                                <button class="btn btn-danger btn-block" onclick="deleteAllGenerations()">
                                    <i class="fas fa-trash-alt"></i> Eliminar TODO el Historial
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Columna derecha (sección principal con cambios) -->
            <div class="col-12 col-lg-8 d-flex flex-column overflow-hidden px-2">
                @php
                    $hasData = false;
                    if ($activeSection === 'all') {
                        $hasData =
                            $totals['videos']['count'] > 0 ||
                            $totals['tryons']['count'] > 0 ||
                            $totals['models']['count'] > 0;
                    } else {
                        $hasData = $totals[$activeSection]['count'] > 0;
                    }
                @endphp
                {{-- Mostrar SIEMPRE el filtro global si estás en sección "all" --}}
                @if ($activeSection === 'all' && $hasData)
                    <div class="card mb-3">
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.generations') }}" class="mb-3">
                                <input type="hidden" name="section" value="all">
                                <div class="input-group">
                                    <input type="number" name="global_user_id" class="form-control"
                                        placeholder="Filtrar por ID de usuario en todas las tablas"
                                        value="{{ $filters['global_user_id'] ?? '' }}">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-search"></i> Filtrar Todo
                                        </button>
                                        @if (isset($filters['global_user_id']))
                                            <a href="{{ route('admin.generations', ['section' => 'all']) }}"
                                                class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Limpiar
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </form>

                            {{-- Estadísticas de filtro global --}}
                            @if (isset($filters['global_user_id']) && isset($globalFilterStats))
                                <div class="alert alert-warning mb-3">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <strong>Generaciones:</strong> {{ $globalFilterStats['count'] }}
                                        </div>
                                        <div class="col-4">
                                            <strong>Tokens:</strong> {{ $globalFilterStats['tokens'] }}
                                        </div>
                                        <div class="col-4">
                                            <strong>Precio:</strong> ${{ number_format($globalFilterStats['price'], 2) }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Mostrar mensaje según casos --}}
                @if (isset($filters['global_user_id']) && $noResultsForGlobalUser)
                    <!-- Usuario no existe -->
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-user-slash fa-5x text-muted mb-4" style="opacity: 0.5;"></i>
                            <h3 class="text-muted">
                                El usuario con CC: <strong>{{ $filters['global_user_id'] }}</strong> no existe en los
                                registros
                            </h3>
                            <p class="text-muted">Prueba con otro ID o elimina el filtro global.</p>
                        </div>
                    </div>
                @elseif(!$hasData && $activeSection === 'all')
                    <!-- Mostrar solo mensaje de bienvenida cuando no hay datos en vista general -->
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <i class="fas fa-magic fa-4x text-primary" style="opacity: 0.3;"></i>
                                </div>
                                <h3 class="text-muted mb-3">¡Bienvenido al Panel de Generaciones!</h3>
                                <p class="text-muted mb-4 lead">Aquí podrás ver y administrar todas las generaciones de IA
                                    de tus usuarios.</p>
                                <div class="row justify-content-center">
                                    <div class="col-md-10">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <div class="card border-primary"
                                                    style="background: rgba(0, 123, 255, 0.05);">
                                                    <div class="card-body text-center">
                                                        <i class="fas fa-film fa-2x text-primary mb-2"></i>
                                                        <h6 class="text-primary">Videos</h6>
                                                        <small class="text-muted">Generación de videos con IA</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="card border-success"
                                                    style="background: rgba(40, 167, 69, 0.05);">
                                                    <div class="card-body text-center">
                                                        <i class="fas fa-tshirt fa-2x text-success mb-2"></i>
                                                        <h6 class="text-success">Try-Ons</h6>
                                                        <small class="text-muted">Pruebas virtuales de ropa</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="card border-info"
                                                    style="background: rgba(23, 162, 184, 0.05);">
                                                    <div class="card-body text-center">
                                                        <i class="fas fa-user-plus fa-2x text-info mb-2"></i>
                                                        <h6 class="text-info">Modelos</h6>
                                                        <small class="text-muted">Modelos virtuales personalizados</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="alert alert-light border"
                                            style="background: rgba(248, 249, 250, 0.8);">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-lightbulb text-warning mr-3 fa-lg"></i>
                                                <div>
                                                    <strong>Tip:</strong> Una vez que los usuarios comiencen a usar las
                                                    herramientas de IA, sus generaciones aparecerán automáticamente en las
                                                    secciones correspondientes.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Mostrar tablas según la sección activa y disponibilidad de datos -->

                    {{-- SECCIÓN DE VIDEOS --}}
                    @if (($activeSection === 'all' && $data['videos']['count'] > 0) || $activeSection === 'videos')
                        @if (isset($data['videos']))
                            <div class="card mb-4">
                                <div
                                    class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-film"></i> Videos Generados
                                        <span class="badge badge-light">{{ $data['videos']['count'] }}</span>
                                    </h5>
                                    @if ($data['videos']['count'] > 0 && $activeSection === 'videos')
                                        <button class="btn btn-light btn-sm"
                                            onclick="deleteGenerationType('video', {{ $filters['video_user_id'] ?? 'null' }})">
                                            <i class="fas fa-trash"></i> Eliminar Historial
                                        </button>
                                    @endif
                                </div>
                                <div class="card-body">
                                    @if ($activeSection === 'videos')
                                        {{-- Filtro solo en vista individual --}}
                                        <form method="GET" action="{{ route('admin.generations') }}" class="mb-3">
                                            <input type="hidden" name="section" value="videos">
                                            <div class="input-group">
                                                <input type="number" name="video_user_id" class="form-control"
                                                    placeholder="Filtrar por ID de usuario"
                                                    value="{{ $filters['video_user_id'] ?? '' }}">
                                                <div class="input-group-append mr-4">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-search"></i> Filtrar
                                                    </button>
                                                    @if (isset($filters['video_user_id']))
                                                        <a href="{{ route('admin.generations', ['section' => 'videos']) }}"
                                                            class="btn btn-secondary">
                                                            <i class="fas fa-times"></i> Limpiar
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </form>
                                    @endif

                                    @if ($data['videos']['count'] > 0)
                                        {{-- Mostrar estadísticas filtradas si hay filtro activo --}}
                                        @if ($activeSection === 'videos' && isset($filters['video_user_id']))
                                            <div class="alert alert-info mb-3">
                                                <div class="row text-center">
                                                    <div class="col-4">
                                                        <strong>Generaciones:</strong>
                                                        {{ count($data['videos']['items']) }}
                                                    </div>
                                                    <div class="col-4">
                                                        <strong>Tokens:</strong> {{ $data['videos']['tokens'] }}
                                                    </div>
                                                    <div class="col-4">
                                                        <strong>Precio:</strong>
                                                        ${{ number_format($data['videos']['price'], 2) }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Usuario</th>
                                                        <th style="min-width: 150px;">Modelo</th>
                                                        <th style="min-width: 20rem;">Prompt</th>
                                                        <th style="min-width: 20rem;">Prompt Negativo</th>

                                                        {{-- Ajuste del header Relación de Aspecto --}}
                                                        <th style="min-width: 180px; white-space: nowrap;">
                                                            Relación de Aspecto
                                                        </th>

                                                        <th>Modo</th>
                                                        <th>Duración</th>
                                                        <th>Estado</th>
                                                        <th>Tokens</th>
                                                        <th>Precio</th>

                                                        {{-- Fecha con ancho fijo para que siempre se vea completa --}}
                                                        <th style="min-width: 180px;">Fecha</th>

                                                        <th>Videos</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($data['videos']['items'] as $video)
                                                        <tr>
                                                            <td><span
                                                                    class="badge badge-secondary">{{ $video->user_id }}</span>
                                                            </td>

                                                            {{-- Modelo truncado --}}
                                                            <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                                title="{{ $video->model_name }}">
                                                                {{ $video->model_name }}
                                                            </td>

                                                            {{-- Prompt truncado --}}
                                                            <td style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                                title="{{ $video->prompt }}">
                                                                {{ $video->prompt }}
                                                            </td>

                                                            {{-- Prompt negativo truncado --}}
                                                            <td style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                                title="{{ $video->negative_prompt ?? 'No definido' }}">
                                                                {{ $video->negative_prompt ?? 'No definido' }}
                                                            </td>

                                                            <td>{{ $video->aspect_ratio }}</td>
                                                            <td><span class="badge badge-info">{{ $video->mode }}</span>
                                                            </td>
                                                            <td>{{ $video->duration }}s</td>
                                                            <td>
                                                                <span
                                                                    class="badge badge-{{ $video->status == 'completed' ? 'success' : 'warning' }}">
                                                                    {{ $video->status }}
                                                                </span>
                                                            </td>
                                                            <td><span
                                                                    class="badge badge-primary">{{ $video->tokens }}</span>
                                                            </td>
                                                            <td><span
                                                                    class="badge badge-secondary">${{ number_format($video->price, 2) }}</span>
                                                            </td>

                                                            {{-- Fecha siempre visible y completa --}}
                                                            <td>{{ $video->created_at->format('d/m/Y H:i') }}</td>

                                                            {{-- Botones en línea --}}
                                                            <td style="white-space: nowrap;">
                                                                @if ($video->result_video_paths)
                                                                    @foreach ($video->result_video_paths as $path)
                                                                        <button class="btn btn-sm btn-outline-primary"
                                                                            style="display: inline-block; margin-right: 3px; margin-bottom: 3px;"
                                                                            onclick="openVideoModal('{{ Storage::url($path) }}')">
                                                                            <i class="fas fa-play"></i> Ver
                                                                        </button>
                                                                    @endforeach
                                                                @endif
                                                            </td>

                                                            <td>
                                                                <button class="btn btn-sm btn-danger"
                                                                    onclick="deleteGeneration('video', {{ $video->id }})">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        {{-- Links de paginación --}}
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <div>
                                                {{ $data['videos']['items']->appends(request()->query())->links('pagination::bootstrap-4') }}
                                            </div>
                                            <div>
                                                Mostrando
                                                <strong>{{ $data['videos']['items']->firstItem() }}</strong>
                                                –
                                                <strong>{{ $data['videos']['items']->lastItem() }}</strong>
                                                de
                                                <strong>{{ $data['videos']['items']->total() }}</strong> resultados
                                                (Página {{ $data['videos']['items']->currentPage() }} de
                                                {{ $data['videos']['items']->lastPage() }})
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-center py-5">
                                            <i class="fas fa-film fa-4x text-muted mb-4" style="opacity: 0.3;"></i>
                                            <h4 class="text-muted mb-3">Aún no hay videos generados</h4>
                                            <p class="text-muted mb-4">Los usuarios pueden subir sus imágenes y generar
                                                videos increíbles.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endif

                    {{-- SECCIÓN DE TRY-ONS --}}
                    @if (($activeSection === 'all' && $data['tryons']['count'] > 0) || $activeSection === 'tryons')
                        @if (isset($data['tryons']))
                            <div class="card mb-4">
                                <div
                                    class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-tshirt"></i> Try-Ons
                                        <span class="badge badge-light">{{ $data['tryons']['count'] }}</span>
                                    </h5>
                                    @if ($data['tryons']['count'] > 0 && $activeSection === 'tryons')
                                        <button class="btn btn-light btn-sm"
                                            onclick="deleteGenerationType('tryon', {{ $filters['tryon_user_id'] ?? 'null' }})">
                                            <i class="fas fa-trash"></i> Eliminar Historial
                                        </button>
                                    @endif
                                </div>
                                <div class="card-body">
                                    @if ($activeSection === 'tryons')
                                        {{-- Filtro solo en vista individual --}}
                                        <form method="GET" action="{{ route('admin.generations') }}" class="mb-3">
                                            <input type="hidden" name="section" value="tryons">
                                            <div class="input-group">
                                                <input type="number" name="tryon_user_id" class="form-control"
                                                    placeholder="Filtrar por ID de usuario"
                                                    value="{{ $filters['tryon_user_id'] ?? '' }}">
                                                <div class="input-group-append mr-4">
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="fas fa-search"></i> Filtrar
                                                    </button>
                                                    @if (isset($filters['tryon_user_id']))
                                                        <a href="{{ route('admin.generations', ['section' => 'tryons']) }}"
                                                            class="btn btn-secondary">
                                                            <i class="fas fa-times"></i> Limpiar
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </form>
                                    @endif

                                    @if ($data['tryons']['count'] > 0)
                                        {{-- Mostrar estadísticas filtradas si hay filtro activo --}}
                                        @if ($activeSection === 'tryons' && isset($filters['tryon_user_id']))
                                            <div class="alert alert-success mb-3">
                                                <div class="row text-center">
                                                    <div class="col-4">
                                                        <strong>Generaciones:</strong>
                                                        {{ count($data['tryons']['items']) }}
                                                    </div>
                                                    <div class="col-4">
                                                        <strong>Tokens:</strong> {{ $data['tryons']['tokens'] }}
                                                    </div>
                                                    <div class="col-4">
                                                        <strong>Precio:</strong>
                                                        ${{ number_format($data['tryons']['price'], 2) }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Usuario</th>
                                                        <th>Modelo</th>
                                                        <th>Tipo de Modelo</th>
                                                        <th>Tipo de Prenda</th>
                                                        <th>Estado</th>
                                                        <th>Tokens</th>
                                                        <th>Precio</th>
                                                        <th>Fecha</th>
                                                        <th>Imágenes</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($data['tryons']['items'] as $tryon)
                                                        <tr>
                                                            <td>
                                                                <span
                                                                    class="badge badge-secondary">{{ $tryon->user_id }}</span>
                                                            </td>
                                                            <td>{{ $tryon->model_name }}</td>
                                                            <td>{{ $tryon->model_type }}</td>
                                                            <td>{{ $tryon->garments_type }}</td>
                                                            <td>
                                                                <span
                                                                    class="badge badge-{{ $tryon->status == 'completed' ? 'success' : 'warning' }}">
                                                                    {{ $tryon->status }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span
                                                                    class="badge badge-primary">{{ $tryon->tokens }}</span>
                                                            </td>
                                                            <td>
                                                                <span
                                                                    class="badge badge-secondary">${{ number_format($tryon->price, 2) }}</span>
                                                            </td>
                                                            <td>{{ $tryon->created_at->format('d/m/Y H:i') }}</td>
                                                            <td>
                                                                @if ($tryon->result_image_paths)
                                                                    @foreach ($tryon->result_image_paths as $path)
                                                                        <button class="btn btn-sm btn-outline-success mb-1"
                                                                            onclick="openImageModal('{{ Storage::url($path) }}', 'Probador Virtual Resultado')">
                                                                            <i class="fas fa-image"></i> Ver
                                                                        </button>
                                                                    @endforeach
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <button class="btn btn-sm btn-danger"
                                                                    onclick="deleteGeneration('tryon', {{ $tryon->id }})">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        {{-- Links de paginación --}}
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                {{ $data['tryons']['items']->appends(request()->query())->links('pagination::bootstrap-4') }}
                                            </div>
                                            <div>
                                                Mostrando
                                                <strong>{{ $data['tryons']['items']->firstItem() }}</strong>
                                                –
                                                <strong>{{ $data['tryons']['items']->lastItem() }}</strong>
                                                de
                                                <strong>{{ $data['tryons']['items']->total() }}</strong> resultados
                                                (Página {{ $data['tryons']['items']->currentPage() }} de
                                                {{ $data['tryons']['items']->lastPage() }})
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-center py-5">
                                            <i class="fas fa-tshirt fa-4x text-muted mb-4" style="opacity: 0.3;"></i>
                                            <h4 class="text-muted mb-3">Aún no hay pruebas virtuales</h4>
                                            <p class="text-muted mb-4">Los usuarios podrán probar virtualmente diferentes
                                                prendas de vestir.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endif

                    {{-- SECCIÓN DE MODELOS --}}
                    @if (($activeSection === 'all' && $data['models']['count'] > 0) || $activeSection === 'models')
                        @if (isset($data['models']))
                            <div class="card mb-4">
                                <div
                                    class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-user"></i> Modelos Virtuales
                                        <span class="badge badge-light">{{ $data['models']['count'] }}</span>
                                    </h5>
                                    @if ($data['models']['count'] > 0 && $activeSection === 'models')
                                        <button class="btn btn-light btn-sm"
                                            onclick="deleteGenerationType('model', {{ $filters['model_user_id'] ?? 'null' }})">
                                            <i class="fas fa-trash"></i> Eliminar Historial
                                        </button>
                                    @endif
                                </div>
                                <div class="card-body">
                                    @if ($activeSection === 'models')
                                        {{-- Filtro solo en vista individual --}}
                                        <form method="GET" action="{{ route('admin.generations') }}" class="mb-3">
                                            <input type="hidden" name="section" value="models">
                                            <div class="input-group">
                                                <input type="number" name="model_user_id" class="form-control"
                                                    placeholder="Filtrar por ID de usuario"
                                                    value="{{ $filters['model_user_id'] ?? '' }}">
                                                <div class="input-group-append mr-4">
                                                    <button type="submit" class="btn btn-info">
                                                        <i class="fas fa-search"></i> Filtrar
                                                    </button>
                                                    @if (isset($filters['model_user_id']))
                                                        <a href="{{ route('admin.generations', ['section' => 'models']) }}"
                                                            class="btn btn-secondary">
                                                            <i class="fas fa-times"></i> Limpiar
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </form>
                                    @endif

                                    @if ($data['models']['count'] > 0)
                                        {{-- Mostrar estadísticas filtradas si hay filtro activo --}}
                                        @if ($activeSection === 'models' && isset($filters['model_user_id']))
                                            <div class="alert alert-info mb-3">
                                                <div class="row text-center">
                                                    <div class="col-4">
                                                        <strong>Generaciones:</strong>
                                                        {{ count($data['models']['items']) }}
                                                    </div>
                                                    <div class="col-4">
                                                        <strong>Tokens:</strong> {{ $data['models']['tokens'] }}
                                                    </div>
                                                    <div class="col-4">
                                                        <strong>Precio:</strong>
                                                        ${{ number_format($data['models']['price'], 2) }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Usuario</th>
                                                        <th>Modelo</th>
                                                        <th>Estado</th>
                                                        <th>Tokens</th>
                                                        <th>Precio</th>
                                                        <th>Fecha</th>
                                                        <th>Imágenes</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($data['models']['items'] as $model)
                                                        <tr>
                                                            <td>
                                                                <span
                                                                    class="badge badge-secondary">{{ $model->user_id }}</span>
                                                            </td>
                                                            <td>{{ $model->model_name }}</td>
                                                            <td>
                                                                <span
                                                                    class="badge badge-{{ $model->status == 'completed' ? 'success' : 'warning' }}">
                                                                    {{ $model->status }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span
                                                                    class="badge badge-primary">{{ $model->tokens }}</span>
                                                            </td>
                                                            <td>
                                                                <span
                                                                    class="badge badge-secondary">${{ number_format($model->price, 2) }}</span>
                                                            </td>
                                                            <td>{{ $model->created_at->format('d/m/Y H:i') }}</td>
                                                            <td>
                                                                @if ($model->result_image_paths)
                                                                    @foreach ($model->result_image_paths as $path)
                                                                        <button class="btn btn-sm btn-outline-info mb-1"
                                                                            onclick="openImageModal('{{ Storage::url($path) }}', 'Modelo Virtual Resultado')">
                                                                            <i class="fas fa-image"></i> Ver
                                                                        </button>
                                                                    @endforeach
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <button class="btn btn-sm btn-danger"
                                                                    onclick="deleteGeneration('model', {{ $model->id }})">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                {{ $data['models']['items']->appends(request()->query())->links('pagination::bootstrap-4') }}
                                            </div>
                                            <div>
                                                Mostrando
                                                <strong>{{ $data['models']['items']->firstItem() }}</strong>
                                                –
                                                <strong>{{ $data['models']['items']->lastItem() }}</strong>
                                                de
                                                <strong>{{ $data['models']['items']->total() }}</strong> resultados
                                                (Página {{ $data['models']['items']->currentPage() }} de
                                                {{ $data['models']['items']->lastPage() }})
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-center py-5">
                                            <i class="fas fa-user-plus fa-4x text-muted mb-4" style="opacity: 0.3;"></i>
                                            <h4 class="text-muted mb-3">Aún no hay modelos virtuales</h4>
                                            <p class="text-muted mb-4">Los usuarios pueden configurar los ajustes y generar
                                                modelos virtuales personalizados.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- MODAL PARA VER RESULTADOS DE IMAGENES -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-images"></i> Vista previa de resultados
                    </h5>
                    <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal"
                        aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="image-viewer-container">
                        <img id="modalImage" src="" alt="Result Image">

                        <!-- 🔍 INDICADOR DE ZOOM -->
                        <div class="zoom-indicator">
                            <i class="fas fa-search-plus"></i> <span id="zoomIndicator">100%</span>
                        </div>

                        <!-- 🔍 AYUDA VISUAL -->
                        <div class="zoom-help">
                            <div><i class="fas fa-mouse"></i> Scroll: Zoom</div>
                            <div><i class="fas fa-hand-rock"></i> Click+Drag: Mover</div>
                            <div><i class="fas fa-mouse"></i> Doble click: Zoom/Reset</div>
                        </div>
                    </div>
                    <div class="image-controls">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="zoom-controls">
                                    <button class="btn btn-primary btn-sm" onclick="zoomImage(0.8)" title="Zoom Out">
                                        <i class="fas fa-search-minus"></i>
                                    </button>
                                    <span id="zoomLevel" class="mx-2 badge badge-secondary">100%</span>
                                    <button class="btn btn-primary btn-sm" onclick="zoomImage(1.25)" title="Zoom In">
                                        <i class="fas fa-search-plus"></i>
                                    </button>
                                    <button class="btn btn-outline-primary btn-sm px-5 ml-2" onclick="resetZoom()"
                                        title="Ajustar a pantalla">
                                        <i class="fas fa-expand-arrows-alt mr-2"></i> Ajustar
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 text-right">
                                <button class="btn btn-success" onclick="downloadImage()" title="Descargar imagen">
                                    <i class="fas fa-download"></i> Descargar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL PARA VER VIDEO -->
    <div class="modal fade" id="videoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-film"></i> Vista previa del video
                    </h5>
                    <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="video-container">
                        <video id="modalVideo" controls>
                            <source src="" type="video/mp4">
                            Tu navegador no soporta la reproducción de videos.
                        </video>
                    </div>
                    <div class="video-controls">
                        <div class="row align-items-center">
                            <div class="col-md-12 text-right">
                                <button class="btn btn-success" onclick="downloadVideo()">
                                    <i class="fas fa-download"></i> Descargar Video
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Variables globales para el modal de imágenes
            let currentImageSrc = '';
            let currentVideoUrl = '';
            let currentZoom = 1;
            let translateX = 0;
            let translateY = 0;
            let isDragging = false;
            let startX = 0;
            let startY = 0;

            // FUNCIONES DE MODAL Y ZOOM PARA IMÁGENES
            const openImageModal = (imageSrc, title) => {
                currentImageSrc = imageSrc;
                currentZoom = 1;
                translateX = 0;
                translateY = 0;
                $('#modalImage').attr('src', imageSrc);
                $('.modal-title').html(`<i class="fas fa-images"></i> ${title}`);
                $('#zoomLevel').text('100%');
                $('#zoomIndicator').text('100%');
                updateImageTransform();
                $('#imageModal').modal('show');
            };

            // 🔍 ZOOM CENTRADO EN EL CURSOR
            const zoomImage = (factor) => {
                currentZoom *= factor;
                currentZoom = Math.max(0.5, Math.min(currentZoom, 5));
                updateImageTransform();
                $('#zoomLevel').text(Math.round(currentZoom * 100) + '%');
                $('#zoomIndicator').text(Math.round(currentZoom * 100) + '%');
            };

            // 🔍 ZOOM CENTRADO EN EL CURSOR CON POSICIÓN DEL MOUSE
            const zoomImageAtCursor = (factor, mouseX, mouseY) => {
                const oldZoom = currentZoom;
                currentZoom *= factor;
                currentZoom = Math.max(0.5, Math.min(currentZoom, 5));

                // Calcular el centro del contenedor de imagen
                const container = $('.image-viewer-container');
                const containerRect = container[0].getBoundingClientRect();
                const containerCenterX = containerRect.width / 2;
                const containerCenterY = containerRect.height / 2;

                // Calcular la posición del mouse relativa al centro del contenedor
                const mouseRelativeX = mouseX - containerRect.left - containerCenterX;
                const mouseRelativeY = mouseY - containerRect.top - containerCenterY;

                // Calcular el nuevo desplazamiento para mantener el zoom centrado en el cursor
                const zoomRatio = currentZoom / oldZoom;
                translateX = mouseRelativeX - (mouseRelativeX - translateX) * zoomRatio;
                translateY = mouseRelativeY - (mouseRelativeY - translateY) * zoomRatio;

                updateImageTransform();
                $('#zoomLevel').text(Math.round(currentZoom * 100) + '%');
                $('#zoomIndicator').text(Math.round(currentZoom * 100) + '%');
            };

            const resetZoom = () => {
                currentZoom = 1;
                translateX = 0;
                translateY = 0;
                updateImageTransform();
                $('#zoomLevel').text('100%');
                $('#zoomIndicator').text('100%');
            };

            const updateImageTransform = () => {
                const image = $('#modalImage');
                image.css({
                    transform: `translate(${translateX}px, ${translateY}px) scale(${currentZoom})`,
                    transition: isDragging ? 'none' : 'transform 0.1s ease-out'
                });
            };

            const downloadImage = () => {
                if (!currentImageSrc) return;

                const link = document.createElement('a');
                link.href = currentImageSrc;
                link.download = `virtual-try-on-result-${Date.now()}.png`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            };

            // EVENTOS DE DRAG Y ZOOM PARA IMÁGENES
            $('#modalImage').on('mousedown', function(e) {
                e.preventDefault();

                // Solo permitir drag si hay zoom
                if (currentZoom <= 1) return;

                isDragging = true;
                startX = e.clientX - translateX;
                startY = e.clientY - translateY;

                // Cambiar cursor y deshabilitar selección
                $(this).css({
                    'cursor': 'grabbing',
                    'user-select': 'none',
                    '-webkit-user-select': 'none',
                    '-moz-user-select': 'none',
                    '-ms-user-select': 'none'
                });

                // Prevenir comportamiento por defecto
                $('.image-viewer-container').addClass('dragging');

                // Prevenir selección de texto durante el drag
                $('body').css('user-select', 'none');
            });

            $(document).on('mousemove', function(e) {
                if (!isDragging) return;

                e.preventDefault();

                // Calcular nueva posición
                const newTranslateX = e.clientX - startX;
                const newTranslateY = e.clientY - startY;

                // Aplicar límites opcionales para evitar que se salga demasiado
                const container = $('.image-viewer-container');
                const containerWidth = container.width();
                const containerHeight = container.height();

                // Límites suaves (opcional)
                const maxTranslateX = containerWidth * 0.5;
                const maxTranslateY = containerHeight * 0.5;

                translateX = Math.max(-maxTranslateX, Math.min(maxTranslateX, newTranslateX));
                translateY = Math.max(-maxTranslateY, Math.min(maxTranslateY, newTranslateY));

                updateImageTransform();
            });

            $(document).on('mouseup', function(e) {
                if (!isDragging) return;

                isDragging = false;

                // Restaurar cursor normal
                $('#modalImage').css({
                    'cursor': currentZoom > 1 ? 'grab' : 'default',
                    'user-select': 'auto',
                    '-webkit-user-select': 'auto',
                    '-moz-user-select': 'auto',
                    '-ms-user-select': 'auto'
                });

                $('.image-viewer-container').removeClass('dragging');
                $('body').css('user-select', 'auto');
            });

            // 🔍 ZOOM CON SCROLL DEL MOUSE - CENTRADO EN CURSOR
            $('#modalImage').on('wheel', function(e) {
                e.preventDefault();

                // Determinar dirección del scroll
                const delta = e.originalEvent.deltaY;
                const factor = delta > 0 ? 0.9 : 1.1;

                // Obtener posición del mouse
                const mouseX = e.clientX;
                const mouseY = e.clientY;

                // Aplicar zoom centrado en el cursor
                zoomImageAtCursor(factor, mouseX, mouseY);
            });

            // 🔍 ZOOM CON BOTONES - CENTRADO EN LA IMAGEN
            window.zoomImage = (factor) => {
                // Para botones, usar el centro de la imagen
                const container = $('.image-viewer-container');
                const containerRect = container[0].getBoundingClientRect();
                const centerX = containerRect.left + containerRect.width / 2;
                const centerY = containerRect.top + containerRect.height / 2;

                zoomImageAtCursor(factor, centerX, centerY);
            };

            // 🖱️ PREVENIR COMPORTAMIENTOS NO DESEADOS
            $('#modalImage').on('contextmenu', function(e) {
                e.preventDefault(); // Prevenir menú contextual
            });

            $('#modalImage').on('dragstart', function(e) {
                e.preventDefault(); // Prevenir drag nativo de la imagen
            });

            // 🔍 INDICADOR VISUAL DE ZOOM
            $('#modalImage').on('mouseenter', function() {
                if (currentZoom > 1) {
                    $(this).css('cursor', 'grab');
                } else {
                    $(this).css('cursor', 'default');
                }
            });

            // 🔍 DOBLE CLICK PARA ZOOM FIT/RESET
            $('#modalImage').on('dblclick', function(e) {
                e.preventDefault();

                if (currentZoom === 1) {
                    // Zoom in al 200% centrado en el cursor
                    const mouseX = e.clientX;
                    const mouseY = e.clientY;
                    zoomImageAtCursor(2, mouseX, mouseY);
                } else {
                    // Reset zoom
                    resetZoom();
                }
            });

            // FUNCIONES PARA MODAL DE VIDEO
            function openVideoModal(videoUrl) {
                currentVideoUrl = videoUrl;
                $('#modalVideo source').attr('src', videoUrl);
                $('#modalVideo')[0].load();
                $('#videoModal').modal('show');
            }

            function downloadVideo() {
                if (!currentVideoUrl) return;

                const link = document.createElement('a');
                link.href = currentVideoUrl;
                link.download = `virtual-try-on-video-${Date.now()}.mp4`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            // FUNCIONES DE ADMINISTRACIÓN (EXISTENTES)
            axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute(
                'content');

            function deleteGeneration(type, id) {
                Swal.fire({
                    title: '¿Eliminar generación?',
                    text: "Esta acción no se puede deshacer",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const url = `/admin/generations/${type}/${id}`;
                        deleteRequest(url, 'Eliminando generación...');
                    }
                });
            }

            function deleteGenerationType(type, userId = null) {
                Swal.fire({
                    title: '¿Eliminar historial?',
                    text: userId ? `Se eliminarán todas las generaciones de tipo ${type} del usuario ${userId}` :
                        `Se eliminarán todas las generaciones de tipo ${type}`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar todo',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const url = '/admin/generations/all';
                        const data = {
                            type,
                            user_id: userId
                        };
                        deleteRequest(url, 'Eliminando historial...', data);
                    }
                });
            }

            function deleteAllGenerations() {
                Swal.fire({
                    title: '¿Eliminar TODO el historial?',
                    text: "Se eliminarán TODAS las generaciones. Esta acción no se puede deshacer.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar todo',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const url = '/admin/generations/all';
                        deleteRequest(url, 'Eliminando todo el historial...');
                    }
                });
            }

            function deleteRequest(url, loadingMessage, data = {}) {
                Swal.fire({
                    title: loadingMessage,
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => Swal.showLoading()
                });

                fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: Object.keys(data).length ? JSON.stringify(data) : null
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Eliminado!',
                                text: data.message,
                                timer: 1500
                            }).then(() => {
                                if (data.user_id) {
                                    const urlParams = new URLSearchParams(window.location.search);
                                    const section = urlParams.get('section') || 'all';
                                    window.location.href = `{{ route('admin.generations') }}?section=${section}`;
                                } else if (data.reload) {
                                    window.location.reload();
                                } else {
                                    window.location.reload();
                                }
                            });
                        } else {
                            throw new Error(data.error || 'Error al eliminar');
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.message || 'Error al procesar la solicitud'
                        });
                    });
            }
        </script>
    @endpush
@endsection
