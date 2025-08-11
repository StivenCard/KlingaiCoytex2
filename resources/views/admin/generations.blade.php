@extends('layouts.probador-virtual-ia')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="main-container d-flex flex-wrap flex-row">
    <!-- LEFT PANEL -->
    <div class="col-12 col-lg-4 d-flex flex-column overflow-hidden px-2" style="align-items:stretch">
        <div class="card">
            <div class="card-header">
                <span class="d-block"><b>Resumen de Generaciones</b></span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="card bg-primary text-white cursor-pointer" onclick="toggleSection('videos-section')">
                            <div class="card-body">
                                <h5 class="card-title">Total Videos</h5>
                                <h2>{{ $totals['videos'] }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="card bg-success text-white cursor-pointer" onclick="toggleSection('tryons-section')">
                            <div class="card-body">
                                <h5 class="card-title">Total Try-Ons</h5>
                                <h2>{{ $totals['tryons'] }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="card bg-info text-white cursor-pointer" onclick="toggleSection('models-section')">
                            <div class="card-body">
                                <h5 class="card-title">Total Modelos</h5>
                                <h2>{{ $totals['models'] }}</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botón Eliminar Todo -->
                @if($totals['videos'] > 0 || $totals['tryons'] > 0 || $totals['models'] > 0)
                <div class="mt-4">
                    @if($userId)
                        <button class="btn btn-danger btn-block" onclick="deleteAllGenerations()">
                            <i class="fas fa-trash-alt"></i> Eliminar Todo el Historial del Usuario {{ $userId }}
                        </button>
                    @else
                        <button class="btn btn-danger btn-block" onclick="deleteAllGenerations()">
                            <i class="fas fa-trash-alt"></i> Eliminar TODO el Historial (Todos los Usuarios)
                        </button>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="col-12 col-lg-8 d-flex flex-column overflow-hidden px-2" style="align-items:stretch">
        <!-- Filtro por usuario -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.generations') }}" class="form-inline">
                    <div class="form-group mr-3">
                        <label for="user_id" class="mr-2">Filtrar por ID de Usuario:</label>
                        <input type="number"
                               name="user_id"
                               id="user_id"
                               value="{{ request('user_id') }}"
                               placeholder="Dejar vacío para ver todos"
                               class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.generations') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Ver Todos
                    </a>
                </form>

                @if($userId)
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-filter"></i>
                        Mostrando generaciones filtradas para el usuario: <strong>{{ $userId }}</strong>
                    </div>
                @else
                    <div class="alert alert-success mt-3">
                        <i class="fas fa-list"></i>
                        Mostrando <strong>todas las generaciones</strong> de todos los usuarios.
                    </div>
                @endif

                @if($totals['videos'] == 0 && $totals['tryons'] == 0 && $totals['models'] == 0)
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle"></i>
                        @if($userId)
                            No se encontraron generaciones para el usuario {{ $userId }}.
                        @else
                            No hay generaciones en el sistema.
                        @endif
                    </div>
                @endif
            </div>
        </div>

        @if($totals['videos'] > 0 || $totals['tryons'] > 0 || $totals['models'] > 0)
        <!-- Sección de Videos -->
        <div id="videos-section" class="card mb-4 section-content" style="display: none;">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-film"></i> Videos Generados
                    <span class="badge badge-primary">{{ $totals['videos'] }}</span>
                </h5>
            </div>
            <div class="card-body">
                @if($generations['videos']->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Usuario</th>
                                <th>Modelo</th>
                                <th>Modo</th>
                                <th>Duración</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Videos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($generations['videos'] as $video)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <span class="badge badge-light">{{ $video->user_id }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-light">{{ $video->model_name}}</span>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">{{ $video->mode }}</span>
                                </td>
                                <td>{{ $video->duration }} segundos</td>
                                <td>
                                    <span class="badge badge-{{ $video->status == 'completed' ? 'success' : 'warning' }}">
                                        {{ $video->status }}
                                    </span>
                                </td>
                                <td>{{ $video->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($video->result_video_paths)
                                        @foreach($video->result_video_paths as $path)
                                        <div class="mb-1">
                                            <a href="{{ Storage::url($path) }}" target="_blank" class="text-primary">
                                                <i class="fas fa-video"></i> {{ basename($path) }}
                                            </a>
                                        </div>
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
                @else
                <div class="text-center py-4">
                    <i class="fas fa-film fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No hay videos generados</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Sección de Try-Ons -->
        <div id="tryons-section" class="card mb-4 section-content" style="display: none;">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-tshirt"></i> Probador Virtual Generaciones
                    <span class="badge badge-success">{{ $totals['tryons'] }}</span>
                </h5>
            </div>
            <div class="card-body">
                @if($generations['tryons']->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Usuario</th>
                                <th>Modelo</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Imágenes</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($generations['tryons'] as $tryon)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <span class="badge badge-light">{{ $tryon->user_id }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-light">{{ $tryon->model_name }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $tryon->status == 'completed' ? 'success' : 'warning' }}">
                                        {{ $tryon->status }}
                                    </span>
                                </td>
                                <td>{{ $tryon->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($tryon->result_image_paths)
                                        @foreach($tryon->result_image_paths as $path)
                                        <div class="mb-1">
                                            <a href="{{ Storage::url($path) }}" target="_blank" class="text-primary">
                                                <i class="fas fa-image"></i> {{ basename($path) }}
                                            </a>
                                        </div>
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
                @else
                <div class="text-center py-4">
                    <i class="fas fa-tshirt fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No hay try-ons generados</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Sección de Modelos -->
        <div id="models-section" class="card section-content" style="display: none;">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user"></i> Modelos Virtuales Generados
                    <span class="badge badge-info">{{ $totals['models'] }}</span>
                </h5>
            </div>
            <div class="card-body">
                @if($generations['models']->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Usuario</th>
                                <th>Modelo</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Imágenes</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($generations['models'] as $model)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <span class="badge badge-light">{{ $model->user_id }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-light">{{ $model->model_name }}</span>
                                <td>
                                    <span class="badge badge-{{ $model->status == 'completed' ? 'success' : 'warning' }}">
                                        {{ $model->status }}
                                    </span>
                                </td>
                                <td>{{ $model->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($model->result_image_paths)
                                        @foreach($model->result_image_paths as $path)
                                        <div class="mb-1">
                                            <a href="{{ Storage::url($path) }}" target="_blank" class="text-primary">
                                                <i class="fas fa-image"></i> {{ basename($path) }}
                                            </a>
                                        </div>
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
                @else
                <div class="text-center py-4">
                    <i class="fas fa-user fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No hay modelos generados</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
<!-- Consumo de API Unificado -->
<div class="card mb-4">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">
            <i class="fas fa-tachometer-alt"></i> Consumo de API
            <button class="btn btn-sm btn-light float-right"
                    onclick="window.location.reload()"
                    title="Actualizar datos">
                <i class="fas fa-sync"></i>
            </button>
        </h5>
    </div>
    <div class="card-body">
        @if(empty($resourcePacks))
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                No se pudo obtener información de consumo de la API
            </div>
        @else
            @foreach($resourcePacks as $pack)
                @php
                    $percentageUsed = ($pack['total_quantity'] > 0)
                        ? (($pack['total_quantity'] - $pack['remaining_quantity']) / $pack['total_quantity']) * 100
                        : 0;

                    $purchaseDate = date('Y-m-d H:i:s', $pack['purchase_time']/1000);
                    $effectiveDate = date('Y-m-d H:i:s', $pack['effective_time']/1000);
                    $expirationDate = date('Y-m-d H:i:s', $pack['invalid_time']/1000);

                    $now = new DateTime(); // Fecha actual
                    $expiration = new DateTime($expirationDate);
                    $daysLeft = $now->diff($expiration)->days;

                    $tokensUsed = $pack['total_quantity'] - $pack['remaining_quantity'];
                    $daysActive = (new DateTime($purchaseDate))->diff($now)->days;
                    $avgDailyUsage = $daysActive > 0 ? $tokensUsed / $daysActive : 0;
                @endphp

                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title text-primary d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-box"></i> {{ $pack['resource_pack_name'] }}
                            </span>
                            <span class="badge badge-{{
                                $pack['status'] === 'online' ? 'success' :
                                ($pack['status'] === 'expired' ? 'danger' : 'warning')
                            }}">
                                {{ ucfirst($pack['status']) }}
                            </span>
                        </h6>

                        <!-- Barra de progreso -->
                        <div class="progress mb-2" style="height: 20px;"
                             title="Consumido: {{ number_format($tokensUsed, 2) }} tokens">
                            <div class="progress-bar {{ $percentageUsed > 80 ? 'bg-danger' : 'bg-success' }}"
                                 role="progressbar"
                                 style="width: {{ $percentageUsed }}%"
                                 aria-valuenow="{{ $percentageUsed }}"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                                {{ number_format($percentageUsed, 1) }}%
                            </div>
                        </div>

                        <!-- Info rápida -->
                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <small class="text-muted d-block">Restante</small>
                                <strong class="text-success">{{ number_format($pack['remaining_quantity'], 2) }}</strong>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Total</small>
                                <strong>{{ number_format($pack['total_quantity'], 2) }}</strong>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Consumo/día</small>
                                <strong class="text-info">{{ number_format($avgDailyUsage, 2) }}</strong>
                            </div>
                        </div>

                        <!-- Fechas -->
                        <div class="timeline small">
                            <div class="mb-1">
                                <i class="fas fa-shopping-cart text-success"></i>
                                <strong>Comprado:</strong> <span class="text-muted">{{ $purchaseDate }}</span>
                            </div>
                            <div class="mb-1">
                                <i class="fas fa-play-circle text-primary"></i>
                                <strong>Activado:</strong> <span class="text-muted">{{ $effectiveDate }}</span>
                            </div>
                            <div class="mb-1">
                                <i class="fas fa-hourglass-end text-danger"></i>
                                <strong>Expira:</strong> <span class="text-muted">
                                    {{ $expirationDate }}
                                    @if($daysLeft > 0)
                                        (en {{ $daysLeft }} días)
                                    @else
                                        (expirado)
                                    @endif
                                </span>
                            </div>
                        </div>

                        @if($daysLeft <= 7 && $daysLeft > 0)
                            <div class="alert alert-warning mt-2 mb-0 py-2 small">
                                <i class="fas fa-exclamation-triangle"></i>
                                Este paquete expirará pronto. Considere renovar.
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            <!-- Resumen Total -->
            <div class="card bg-light mb-4">
                <div class="card-body">
                    <h6 class="card-title">Resumen Total</h6>
                    @php
                        $totalTokens = 0;
                        $totalRemaining = 0;
                        $totalUsed = 0;

                        foreach($resourcePacks as $pack) {
                            $totalTokens += $pack['total_quantity'];
                            $totalRemaining += $pack['remaining_quantity'];
                            $totalUsed += ($pack['total_quantity'] - $pack['remaining_quantity']);
                        }
                    @endphp
                    <div class="row text-center">
                        <div class="col-4">
                            <small class="text-muted d-block">Total Tokens</small>
                            <strong>{{ number_format($totalTokens, 2) }}</strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">Consumidos</small>
                            <strong class="text-danger">{{ number_format($totalUsed, 2) }}</strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">Disponibles</small>
                            <strong class="text-success">{{ number_format($totalRemaining, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Costos Estimados -->
            <div class="card">
                <div class="card-body">
                    <h6>Costos por Operación:</h6>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Video (5s) - Estándar
                            <span class="badge badge-primary">2 tokens ($0.28)</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Video (10s) - Estándar
                            <span class="badge badge-primary">4 tokens ($0.56)</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Video (5s) - Profesional
                            <span class="badge badge-primary">3.5 tokens ($0.49)</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Video (10s) - Profesional
                            <span class="badge badge-primary">7 tokens ($0.98)</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Try-on
                            <span class="badge badge-success">1 token ($0.07)</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Modelo Virtual - Texto a Imagen
                            <span class="badge badge-info">4 tokens ($0.014)</span>
                        </li>
                    </ul>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Agregar estilos para el cursor pointer en las cards
document.querySelectorAll('.cursor-pointer').forEach(card => {
    card.style.cursor = 'pointer';
});

function toggleSection(sectionId) {
    // Ocultar todas las secciones
    document.querySelectorAll('.section-content').forEach(section => {
        section.style.display = 'none';
    });

    // Mostrar la sección seleccionada
    const selectedSection = document.getElementById(sectionId);
    if (selectedSection) {
        selectedSection.style.display = 'block';
    }
}

function deleteGeneration(type, id) {
    const userId = {{ $userId ?? 'null' }};

    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const loadingAlert = Swal.fire({
                title: 'Eliminando...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Construir la URL con parámetros
            let url = `/admin/generations/${type}/${id}`;
            if (userId) {
                url += `?user_id=${userId}`;
            }

            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
            })
            .then(response => response.json())
            .then(data => {
                loadingAlert.close();
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Eliminado!',
                        text: data.message,
                        timer: 1500
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.error || 'Error al eliminar');
                }
            })
            .catch(error => {
                loadingAlert.close();
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Error al eliminar las generaciones'
                });
            });
        }
    });
}

function deleteAllGenerations() {
    const userId = {{ $userId ?? 'null' }};

    let confirmMessage, processingMessage;

    if (userId) {
        confirmMessage = `Se eliminarán TODAS las generaciones del usuario ${userId}. Esta acción no se puede deshacer.`;
        processingMessage = `Eliminando todas las generaciones del usuario ${userId}...`;
    } else {
        confirmMessage = 'Se eliminarán TODAS las generaciones de TODOS los usuarios. Esta acción no se puede deshacer.';
        processingMessage = 'Eliminando todas las generaciones del sistema...';
    }

    Swal.fire({
        title: '¿Estás seguro?',
        text: confirmMessage,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar todo',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const loadingAlert = Swal.fire({
                title: processingMessage,
                text: 'Por favor espere',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const requestBody = userId ? { user_id: userId } : {};

            fetch('/admin/generations/all', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(requestBody)
            })
            .then(response => response.json())
            .then(data => {
                loadingAlert.close();
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Eliminado!',
                        text: data.message,
                        timer: 1500
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.error || 'Error al eliminar');
                }
            })
            .catch(error => {
                loadingAlert.close();
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Error al eliminar las generaciones'
                });
            });
        }
    });
}

// Mostrar la primera sección por defecto si hay datos
document.addEventListener('DOMContentLoaded', function() {
    @if($totals['videos'] > 0 || $totals['tryons'] > 0 || $totals['models'] > 0)
        // Mostrar la sección con más contenido
        @if($totals['videos'] > 0)
            toggleSection('videos-section');
        @elseif($totals['tryons'] > 0)
            toggleSection('tryons-section');
        @elseif($totals['models'] > 0)
            toggleSection('models-section');
        @endif
    @endif
});
</script>
@endpush
@endsection
