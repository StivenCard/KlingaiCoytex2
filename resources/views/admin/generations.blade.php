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
                <div class="mt-4">
                    <button class="btn btn-danger btn-block" onclick="deleteAllGenerations()">
                        <i class="fas fa-trash-alt"></i> Eliminar Todo el Historial
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="col-12 col-lg-8 d-flex flex-column overflow-hidden px-2" style="align-items:stretch">
        <!-- Filtro por usuario -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.generations') }}" class="form-inline">
                    <input type="number" name="user_id" value="{{ request('user_id') }}" placeholder="ID de usuario" class="form-control mr-2">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="{{ route('admin.generations') }}" class="btn btn-secondary ml-2">Ver todos</a>
                </form>
            </div>
        </div>
        <!-- Sección de Videos -->
        <div id="videos-section" class="card mb-4 section-content" style="display: none;">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-film"></i> Videos Generados</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Videos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($generations['videos'] as $video)
                            <tr>
                                <td>{{ $video->id }}</td>
                                <td>
                                    <span class="badge badge-{{ $video->status == 'completed' ? 'success' : 'warning' }}">
                                        {{ $video->status }}
                                    </span>
                                </td>
                                <td>{{ $video->created_at }}</td>
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
            </div>
        </div>

        <!-- Sección de Try-Ons -->
        <div id="tryons-section" class="card mb-4 section-content" style="display: none;">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-tshirt"></i> Probador Virtual Generaciones</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Imágenes</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($generations['tryons'] as $tryon)
                            <tr>
                                <td>{{ $tryon->id }}</td>
                                <td>
                                    <span class="badge badge-{{ $tryon->status == 'completed' ? 'success' : 'warning' }}">
                                        {{ $tryon->status }}
                                    </span>
                                </td>
                                <td>{{ $tryon->created_at }}</td>
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
            </div>
        </div>

        <!-- Sección de Modelos -->
        <div id="models-section" class="card section-content" style="display: none;">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user"></i> Modelos Virtuales Generados</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Imágenes</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($generations['models'] as $model)
                            <tr>
                                <td>{{ $model->id }}</td>
                                <td>
                                    <span class="badge badge-{{ $model->status == 'completed' ? 'success' : 'warning' }}">
                                        {{ $model->status }}
                                    </span>
                                </td>
                                <td>{{ $model->created_at }}</td>
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
            </div>
        </div>
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

            // Construir la URL manualmente
            const url = `/admin/generations/${type}/${id}`;

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
                    text: error.message || 'Error al eliminar la generación'
                });
            });
        }
    });
}

function deleteAllGenerations() {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Se eliminarán TODAS las generaciones. Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar todo',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const loadingAlert = Swal.fire({
                title: 'Eliminando todas las generaciones...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('/admin/generations/all', {
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

// Mostrar la primera sección por defecto
document.addEventListener('DOMContentLoaded', function() {
    toggleSection('videos-section');
});
</script>
@endpush
@endsection
