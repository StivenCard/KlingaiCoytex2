@extends('layouts.probador-virtual-ia')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="main-container d-flex flex-wrap flex-row">
    <!-- LEFT PANEL -->
    <div class="col-12 col-lg-4 d-flex flex-column overflow-hidden px-2">
        <!-- Resumen de Generaciones -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Resumen de Generaciones</h5>
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

                <!-- Botón Global de Eliminar -->
                @if($totals['videos'] > 0 || $totals['tryons'] > 0 || $totals['models'] > 0)
                    <div class="mt-4">
                        <button class="btn btn-danger btn-block" onclick="deleteAllGenerations()">
                            <i class="fas fa-trash-alt"></i> Eliminar TODO el Historial
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Consumo de API -->
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
                @if(empty($resourcePacks))
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        No se pudo obtener información de consumo de la API
                    </div>
                @else
                    @foreach($resourcePacks as $pack)
                        <div class="card mb-3 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title d-flex justify-content-between align-items-center">
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
                                     title="Consumido: {{ number_format($pack['used_quantity'], 2) }} tokens">
                                    <div class="progress-bar {{ $pack['percentage_used'] > 80 ? 'bg-danger' : 'bg-success' }}"
                                         role="progressbar"
                                         style="width: {{ $pack['percentage_used'] }}%">
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
                                        <span class="text-muted">{{ $pack['formatted_dates']['expiration'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Costos por Operación -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Costos por Operación</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-film"></i> Video (5s) - Estándar
                                        <small class="d-block text-muted">2 tokens</small>
                                    </div>
                                    <span class="badge badge-primary">$0.28</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-film"></i> Video (10s) - Estándar
                                        <small class="d-block text-muted">4 tokens</small>
                                    </div>
                                    <span class="badge badge-primary">$0.56</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-film"></i> Video (5s) - Pro
                                        <small class="d-block text-muted">3.5 tokens</small>
                                    </div>
                                    <span class="badge badge-primary">$0.49</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-film"></i> Video (10s) - Pro
                                        <small class="d-block text-muted">7 tokens</small>
                                    </div>
                                    <span class="badge badge-primary">$0.98</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-tshirt"></i> Try-on
                                        <small class="d-block text-muted">1 token</small>
                                    </div>
                                    <span class="badge badge-success">$0.07</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-image"></i> Modelo Virtual
                                        <small class="d-block text-muted">4 tokens</small>
                                    </div>
                                    <span class="badge badge-info">$0.014</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="col-12 col-lg-8 d-flex flex-column overflow-hidden px-2">
        <!-- Sección de Videos -->
        @if($data['videos']['count'] > 0)
            <div id="videos-section" class="card mb-4 section-content">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-film"></i> Videos Generados
                        <span class="badge badge-light">{{ $data['videos']['count'] }}</span>
                    </h5>
                    <button class="btn btn-light btn-sm"
                            onclick="deleteGenerationType('video', {{ $filters['video_user_id'] ?? 'null' }})">
                        <i class="fas fa-trash"></i> Eliminar Historial
                    </button>
                </div>
                <div class="card-body">
                    <!-- Filtro específico para videos -->
                    <form method="GET" action="{{ route('admin.generations') }}" class="mb-3">
                        <div class="input-group">
                            <input type="number"
                                   name="video_user_id"
                                   class="form-control"
                                   placeholder="Filtrar por ID de usuario"
                                   value="{{ $filters['video_user_id'] ?? '' }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                @if(isset($filters['video_user_id']))
                                    <a href="{{ route('admin.generations') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Limpiar
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
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
                                @foreach($data['videos']['items'] as $video)
                                    <tr>
                                        <td>
                                            <span class="badge badge-secondary">{{ $video->user_id }}</span>
                                        </td>
                                        <td>{{ $video->model_name }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ $video->mode }}</span>
                                        </td>
                                        <td>{{ $video->duration }}s</td>
                                        <td>
                                            <span class="badge badge-{{ $video->status == 'completed' ? 'success' : 'warning' }}">
                                                {{ $video->status }}
                                            </span>
                                        </td>
                                        <td>{{ $video->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($video->result_video_paths)
                                                @foreach($video->result_video_paths as $path)
                                                    <a href="{{ Storage::url($path) }}"
                                                       target="_blank"
                                                       class="btn btn-sm btn-outline-primary mb-1">
                                                        <i class="fas fa-play"></i> Ver
                                                    </a>
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
                </div>
            </div>
        @endif

        <!-- Sección de Try-Ons -->
        @if($data['tryons']['count'] > 0)
            <div id="tryons-section" class="card mb-4 section-content">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-tshirt"></i> Try-Ons
                        <span class="badge badge-light">{{ $data['tryons']['count'] }}</span>
                    </h5>
                    <button class="btn btn-light btn-sm"
                            onclick="deleteGenerationType('tryon', {{ $filters['tryon_user_id'] ?? 'null' }})">
                        <i class="fas fa-trash"></i> Eliminar Historial
                    </button>
                </div>
                <div class="card-body">
                    <!-- Filtro específico para try-ons -->
                    <form method="GET" action="{{ route('admin.generations') }}" class="mb-3">
                        <div class="input-group">
                            <input type="number"
                                   name="tryon_user_id"
                                   class="form-control"
                                   placeholder="Filtrar por ID de usuario"
                                   value="{{ $filters['tryon_user_id'] ?? '' }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                @if(isset($filters['tryon_user_id']))
                                    <a href="{{ route('admin.generations') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Limpiar
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Modelo</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Imágenes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['tryons']['items'] as $tryon)
                                    <tr>
                                        <td>
                                            <span class="badge badge-secondary">{{ $tryon->user_id }}</span>
                                        </td>
                                        <td>{{ $tryon->model_name }}</td>
                                        <td>
                                            <span class="badge badge-{{ $tryon->status == 'completed' ? 'success' : 'warning' }}">
                                                {{ $tryon->status }}
                                            </span>
                                        </td>
                                        <td>{{ $tryon->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($tryon->result_image_paths)
                                                @foreach($tryon->result_image_paths as $path)
                                                    <a href="{{ Storage::url($path) }}"
                                                       target="_blank"
                                                       class="btn btn-sm btn-outline-success mb-1">
                                                        <i class="fas fa-image"></i> Ver
                                                    </a>
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
                </div>
            </div>
        @endif

        <!-- Sección de Modelos -->
        @if($data['models']['count'] > 0)
            <div id="models-section" class="card mb-4 section-content">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user"></i> Modelos Virtuales
                        <span class="badge badge-light">{{ $data['models']['count'] }}</span>
                    </h5>
                    <button class="btn btn-light btn-sm"
                            onclick="deleteGenerationType('model', {{ $filters['model_user_id'] ?? 'null' }})">
                        <i class="fas fa-trash"></i> Eliminar Historial
                    </button>
                </div>
                <div class="card-body">
                    <!-- Filtro específico para modelos -->
                    <form method="GET" action="{{ route('admin.generations') }}" class="mb-3">
                        <div class="input-group">
                            <input type="number"
                                   name="model_user_id"
                                   class="form-control"
                                   placeholder="Filtrar por ID de usuario"
                                   value="{{ $filters['model_user_id'] ?? '' }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                @if(isset($filters['model_user_id']))
                                    <a href="{{ route('admin.generations') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Limpiar
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Modelo</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Imágenes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['models']['items'] as $model)
                                    <tr>
                                        <td>
                                            <span class="badge badge-secondary">{{ $model->user_id }}</span>
                                        </td>
                                        <td>{{ $model->model_name }}</td>
                                        <td>
                                            <span class="badge badge-{{ $model->status == 'completed' ? 'success' : 'warning' }}">
                                                {{ $model->status }}
                                            </span>
                                        </td>
                                        <td>{{ $model->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($model->result_image_paths)
                                                @foreach($model->result_image_paths as $path)
                                                    <a href="{{ Storage::url($path) }}"
                                                       target="_blank"
                                                       class="btn btn-sm btn-outline-info mb-1">
                                                        <i class="fas fa-image"></i> Ver
                                                    </a>
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
                </div>
            </div>
        @endif

        @if($data['videos']['count'] == 0 && $data['tryons']['count'] == 0 && $data['models']['count'] == 0)
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No hay generaciones para mostrar.
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function toggleSection(sectionId) {
    document.querySelectorAll('.section-content').forEach(section => {
        section.style.display = 'none';
    });

    const selectedSection = document.getElementById(sectionId);
    if (selectedSection) {
        selectedSection.style.display = 'block';
    }
}

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
        text: userId ? `Se eliminarán todas las generaciones de tipo ${type} del usuario ${userId}`
                    : `Se eliminarán todas las generaciones de tipo ${type}`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar todo',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const url = '/admin/generations/all';
            const data = { type, user_id: userId };
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
            }).then(() => window.location.reload());
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

// Mostrar la primera sección con contenido al cargar
document.addEventListener('DOMContentLoaded', function() {
    const sections = ['videos', 'tryons', 'models'];
    for (const section of sections) {
        if (document.querySelector(`#${section}-section .table-responsive`)) {
            toggleSection(`${section}-section`);
            break;
        }
    }
});
</script>
@endpush
@endsection
