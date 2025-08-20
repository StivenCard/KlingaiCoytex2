@extends('layouts.probador-virtual-ia')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="main-container d-flex flex-wrap flex-row">
    <div class="col-12 col-lg-4 d-flex flex-column overflow-hidden px-2">
        <!-- Panel de Navegación por Secciones -->
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-list"></i> Navegación</h5>
            </div>
            <div class="card-body">
                <div class="btn-group-vertical w-100" role="group">
                    <a href="{{ route('admin.generations', ['section' => 'all']) }}" 
                       class="btn {{ $activeSection === 'all' ? 'btn-primary' : 'btn-outline-primary' }} mb-2">
                        <i class="fas fa-th-large"></i> Vista General
                    </a>
                    <a href="{{ route('admin.generations', ['section' => 'videos']) }}" 
                       class="btn {{ $activeSection === 'videos' ? 'btn-primary' : 'btn-outline-primary' }} mb-2">
                        <i class="fas fa-film"></i> Solo Videos ({{ $totals['videos'] }})
                    </a>
                    <a href="{{ route('admin.generations', ['section' => 'tryons']) }}" 
                       class="btn {{ $activeSection === 'tryons' ? 'btn-success' : 'btn-outline-success' }} mb-2">
                        <i class="fas fa-tshirt"></i> Solo Try-Ons ({{ $totals['tryons'] }})
                    </a>
                    <a href="{{ route('admin.generations', ['section' => 'models']) }}" 
                       class="btn {{ $activeSection === 'models' ? 'btn-info' : 'btn-outline-info' }}">
                        <i class="fas fa-user"></i> Solo Modelos ({{ $totals['models'] }})
                    </a>
                </div>
            </div>
        </div>

        <!-- Resumen de Generaciones -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Resumen Total</h5>
            </div>
            <div class="card-body"> 
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="card bg-primary text-white"> 
                            <div class="card-body">
                                <h5 class="card-title">Total Videos</h5>
                                <h2>{{ $totals['videos'] }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="card bg-success text-white">
                            <div class="card-body"> 
                                <h5 class="card-title">Total Try-Ons</h5> 
                                <h2>{{ $totals['tryons'] }}</h2> 
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-3"> 
                        <div class="card bg-info text-white"> 
                            <div class="card-body"> 
                                <h5 class="card-title">Total Modelos</h5> 
                                <h2>{{ $totals['models'] }}</h2> 
                            </div>
                        </div>
                    </div>
                </div>

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
                                    <span class="badge badge-{{ $pack['status'] === 'online' ? 'success' : ($pack['status'] === 'expired' ? 'danger' : 'warning') }}"> 
                                        {{ ucfirst($pack['status']) }} 
                                    </span>
                                </h6>

                                <div class="progress mb-2" style="height: 20px;" title="Consumido: {{ number_format($pack['used_quantity'], 2) }} tokens"> 
                                    <div class="progress-bar {{ $pack['percentage_used'] > 80 ? 'bg-danger' : 'bg-success' }}" role="progressbar" style="width: {{ $pack['percentage_used'] }}%"> 
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
                                        <span class="text-muted">{{ $pack['formatted_dates']['expiration'] }}</span> 
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8 d-flex flex-column overflow-hidden px-2">
        {{-- SECCIÓN DE VIDEOS --}}
        @if($activeSection === 'all' || $activeSection === 'videos')
            @if(isset($data['videos']))
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center"> 
                        <h5 class="mb-0">
                            <i class="fas fa-film"></i> Videos Generados
                            <span class="badge badge-light">{{ $data['videos']['count'] }}</span> 
                        </h5>
                        @if($data['videos']['count'] > 0 && $activeSection === 'videos')
                            <button class="btn btn-light btn-sm"
                                    onclick="deleteGenerationType('video', {{ $filters['video_user_id'] ?? 'null' }})">
                                <i class="fas fa-trash"></i> Eliminar Historial 
                            </button>
                        @endif
                    </div>
                    <div class="card-body"> 
                        @if($activeSection === 'videos')
                            {{-- Filtro solo en vista individual --}}
                            <form method="GET" action="{{ route('admin.generations') }}" class="mb-3">
                                <input type="hidden" name="section" value="videos">
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
                                            <a href="{{ route('admin.generations', ['section' => 'videos']) }}" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Limpiar 
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        @endif

                        @if($data['videos']['count'] > 0)
                            <div class="table-responsive"> 
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Usuario</th> 
                                            <th>Modelo</th> 
                                            <th style="min-width: 25rem">Prompt</th> 
                                            <th>Prompt Negativo</th> 
                                            <th>Relacion de Aspecto</th> 
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
                                                <td>{{ $video->prompt }}</td> 
                                                <td>{{ $video->negative_prompt ?? 'No definido' }}</td> 
                                                <td>{{ $video->aspect_ratio }}</td> 
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
                        @else
                            <div class="text-center py-5"> 
                                <i class="fas fa-film fa-4x text-muted mb-4" style="opacity: 0.3;"></i> 
                                <h4 class="text-muted mb-3">Aún no hay videos generados</h4> 
                                <p class="text-muted mb-4">Los usuarios pueden subir sus imágenes y generar videos increíbles.</p> 
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        @endif

        {{-- SECCIÓN DE TRY-ONS --}}
        @if($activeSection === 'all' || $activeSection === 'tryons')
            @if(isset($data['tryons']))
                <div class="card mb-4">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center"> 
                        <h5 class="mb-0">
                            <i class="fas fa-tshirt"></i> Try-Ons
                            <span class="badge badge-light">{{ $data['tryons']['count'] }}</span> 
                        </h5>
                        @if($data['tryons']['count'] > 0 && $activeSection === 'tryons')
                            <button class="btn btn-light btn-sm"
                                    onclick="deleteGenerationType('tryon', {{ $filters['tryon_user_id'] ?? 'null' }})">
                                <i class="fas fa-trash"></i> Eliminar Historial 
                            </button>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($activeSection === 'tryons')
                            {{-- Filtro solo en vista individual --}}
                            <form method="GET" action="{{ route('admin.generations') }}" class="mb-3">
                                <input type="hidden" name="section" value="tryons">
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
                                            <a href="{{ route('admin.generations', ['section' => 'tryons']) }}" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Limpiar 
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        @endif

                        @if($data['tryons']['count'] > 0)
                            <div class="table-responsive"> 
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Usuario</th> 
                                            <th>Modelo</th> 
                                            <th>Tipo de Modelo</th> 
                                            <th>Tipo de Prenda</th> 
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
                                                <td>{{ $tryon->model_type }}</td> 
                                                <td>{{ $tryon->garments_type }}</td> 
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
                        @else
                            <div class="text-center py-5"> 
                                <i class="fas fa-tshirt fa-4x text-muted mb-4" style="opacity: 0.3;"></i>
                                <h4 class="text-muted mb-3">Aún no hay pruebas virtuales</h4>
                                <p class="text-muted mb-4">Los usuarios podrán probar virtualmente diferentes prendas de vestir.</p> 
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        @endif

        {{-- SECCIÓN DE MODELOS --}}
        @if($activeSection === 'all' || $activeSection === 'models')
            @if(isset($data['models']))
                <div class="card mb-4">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center"> 
                        <h5 class="mb-0">
                            <i class="fas fa-user"></i> Modelos Virtuales
                            <span class="badge badge-light">{{ $data['models']['count'] }}</span> 
                        </h5>
                        @if($data['models']['count'] > 0 && $activeSection === 'models')
                            <button class="btn btn-light btn-sm"
                                    onclick="deleteGenerationType('model', {{ $filters['model_user_id'] ?? 'null' }})">
                                <i class="fas fa-trash"></i> Eliminar Historial 
                            </button>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($activeSection === 'models')
                            {{-- Filtro solo en vista individual --}}
                            <form method="GET" action="{{ route('admin.generations') }}" class="mb-3">
                                <input type="hidden" name="section" value="models">
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
                                            <a href="{{ route('admin.generations', ['section' => 'models']) }}" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Limpiar 
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        @endif

                        @if($data['models']['count'] > 0)
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
                        @else
                            <div class="text-center py-5"> 
                                <i class="fas fa-user-plus fa-4x text-muted mb-4" style="opacity: 0.3;"></i>
                                <h4 class="text-muted mb-3">Aún no hay modelos virtuales</h4> 
                                <p class="text-muted mb-4">Los usuarios pueden configurar los ajustes y generar modelos virtuales personalizados.</p> 
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        @endif

        {{-- ESTADO VACÍO GLOBAL SOLO PARA VISTA GENERAL --}}
        @if($activeSection === 'all' && $totals['videos'] == 0 && $totals['tryons'] == 0 && $totals['models'] == 0)
            <div class="card">
                <div class="card-body"> 
                    <div class="text-center py-5"> 
                        <div class="mb-4">
                            <i class="fas fa-magic fa-4x text-primary" style="opacity: 0.3;"></i> 
                        </div>
                        <h3 class="text-muted mb-3">¡Bienvenido al Panel de Generaciones!</h3> 
                        <p class="text-muted mb-4 lead">Aquí podrás ver y administrar todas las generaciones de IA de tus usuarios.</p> 
                        <div class="row justify-content-center"> 
                            <div class="col-md-10">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="card border-primary" style="background: rgba(0, 123, 255, 0.05);">
                                            <div class="card-body text-center"> 
                                                <i class="fas fa-film fa-2x text-primary mb-2"></i>
                                                <h6 class="text-primary">Videos</h6>
                                                <small class="text-muted">Generación de videos con IA</small> 
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3"> 
                                        <div class="card border-success" style="background: rgba(40, 167, 69, 0.05);"> 
                                            <div class="card-body text-center"> 
                                                <i class="fas fa-tshirt fa-2x text-success mb-2"></i> 
                                                <h6 class="text-success">Try-Ons</h6> 
                                                <small class="text-muted">Pruebas virtuales de ropa</small> 
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3"> 
                                        <div class="card border-info" style="background: rgba(23, 162, 184, 0.05);"> 
                                            <div class="card-body text-center"> 
                                                <i class="fas fa-user-plus fa-2x text-info mb-2"></i> 
                                                <h6 class="text-info">Modelos</h6> 
                                                <small class="text-muted">Modelos virtuales personalizados</small> 
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-light border" style="background: rgba(248, 249, 250, 0.8);"> 
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-lightbulb text-warning mr-3 fa-lg"></i>
                                        <div>
                                            <strong>Tip:</strong> Una vez que los usuarios comiencen a usar las herramientas de IA, sus generaciones aparecerán automáticamente en las secciones correspondientes. 
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content'); 

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
            text: userId ? `Se eliminarán todas las generaciones de tipo ${type} del usuario ${userId}` : `Se eliminarán todas las generaciones de tipo ${type}`,
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
                }).then(() => {
                    if (data.reload) {
                        window.location.reload();
                    } else {
                        // Solo recargar si no hay indicador específico
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