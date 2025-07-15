@extends('layouts.app')

@section('content')
<div class="main-container">
    <!-- LEFT PANEL -->
    <div class="left-panel">
        <!-- TABS -->
        <span class="d-block m-2 "><b>Selecciona un Modelo</b></span>
        <div class="sub-tabs">
            <button class="btn btn-outline-secondary btn-sm sub-tab" data-tab="virtual">Virtual</button>
            <button class="btn btn-secondary btn-sm sub-tab active" data-tab="default">Predeterminado</button>
            <button class="btn btn-outline-secondary btn-sm sub-tab mr-2" data-tab="upload">Subir</button>
            <button class="btn btn-outline-info btn-sm info-btn" id="modelInfoBtn" title="Guía de Modelos">
                <i class="fas fa-info-circle"></i>
            </button>
        </div>

        <!-- SCROLL CONTAINER -->
        <div class="scroll-container">
            <!-- VIRTUAL MODELS -->
            <div class="tab-content d-none" data-content="virtual">
                <div class="grid-3" id="virtualModelGrid">
                    @forelse($virtualModels as $model)
                        @foreach($model->all_preview_urls as $index => $imageUrl)
                            <div class="item" data-model="{{ $model->id }}" data-index="{{ $index }}">
                                <img src="{{ $imageUrl }}" alt="Image {{ $index + 1 }} - {{ $model->display_name }}">
                                <input type="radio" name="selected_virtual_model" value="{{ $model->id }}" class="d-none">
                                <input type="radio" name="selected_virtual_index" value="{{ $index }}" class="d-none">
                                <div class="overlay">
                                    <div class="badges">
                                        <span class="badge badge-primary {{ $model->gender }}">{{ ucfirst($model->gender) }}</span>
                                        <span class="badge badge-secondary">{{ ucfirst($model->age_group) }}</span>
                                        <span class="badge badge-info">{{ ucfirst($model->skin_tone) }}</span>
                                    </div>
                                    <div class="overlay-title">{{ $model->display_name }} - {{ $index + 1 }}</div>
                                    <div class="overlay-date">{{ $model->formatted_date }}</div>
                                </div>
                            </div>
                        @endforeach
                    @empty
                        <div class="no-images">
                            <i class="fas fa-user" style="color: #141414;"></i>
                            <p style="color: #141414;">No hay modelos virtuales disponibles</p>
                            <small>
                                <a href="{{ route('virtual-model') }}" class="btn btn-outline-primary">
                                    Genere primero modelos virtuales
                                </a>
                            </small>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- DEFAULT MODELS -->
            <div class="tab-content" data-content="default">
                <div class="grid-3" id="defaultModelGrid">
                    @forelse($defaultModels as $model)
                        <div class="item" data-model="{{ $model['filename'] }}">
                            <img src="{{ $model['url'] }}" alt="{{ $model['name'] }}">
                            <input type="radio" name="selected_default_model" value="{{ $model['filename'] }}" class="d-none">
                        </div>
                    @empty
                        <div class="no-images">
                            <i class="fas fa-images"></i>
                            <p>No hay modelos por defecto disponibles</p>
                            <small>Coloca imágenes en public/klingai/default_models/</small>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- UPLOAD -->
            <div class="tab-content d-none" data-content="upload">
                <div class="upload" id="humanUploadArea" onclick="triggerFileInput('humanImageInput')">
                    <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                    <div class="upload-text">Subir Modelo Humano</div>
                    <input type="file" id="humanImageInput" name="human_image" accept=".jpg,.jpeg,.png" class="file-input">
                    <div class="preview" id="humanPreview">
                        <img src="" alt="Preview">
                        <button class="btn btn-danger btn-sm remove-btn" onclick="clearFileInput('humanImageInput', 'humanUploadArea', 'humanPreview')">
                            <i class="fas fa-times"></i>
                        </button>
                        <button class="btn btn-primary btn-sm reupload-btn" onclick="triggerFileInput('humanImageInput')">
                            <i class="fas fa-upload"></i> Re-subir
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- GARMENT SECTION -->
        <div class="section">
            <div class="tabs-container">
                <div class="btn-group btn-group-toggle tabs" data-toggle="buttons">
                    <label class="btn btn-outline-primary btn-sm active mr-1">
                        <input type="radio" name="garment_type" id="single" autocomplete="off" checked data-garment="single"> Prenda Única
                    </label>
                    <label class="btn btn-outline-primary btn-sm">
                        <input type="radio" name="garment_type" id="multiple" autocomplete="off" data-garment="multiple"> Múltiples Prendas
                    </label>
                </div>
                <button class="btn btn-outline-info btn-sm info-btn" id="garmentInfoBtn" title="Guía de Prendas">
                    <i class="fas fa-info-circle"></i>
                </button>
            </div>

            <!-- SINGLE GARMENT -->
            <div class="garment-content" data-garment-content="single">
                <div class="upload" id="singleUploadArea" onclick="triggerFileInput('singleGarmentInput')">
                    <div class="upload-icon"><i class="fas fa-tshirt"></i></div>
                    <div class="upload-text">Subir prenda única</div>
                    <input type="file" id="singleGarmentInput" name="single_garment" accept=".jpg,.jpeg,.png" class="file-input">
                    <div class="preview" id="singlePreview">
                        <img src="" alt="Preview">
                        <button class="btn btn-danger btn-sm remove-btn" onclick="clearFileInput('singleGarmentInput', 'singleUploadArea', 'singlePreview')">
                            <i class="fas fa-times"></i>
                        </button>
                        <button class="btn btn-primary btn-sm reupload-btn" onclick="triggerFileInput('singleGarmentInput')">
                            <i class="fas fa-upload"></i> Re-subir
                        </button>
                    </div>
                </div>
            </div>

            <!-- MULTIPLE GARMENTS -->
            <div class="garment-content d-none" data-garment-content="multiple">
                <div class="upload" id="topUploadArea" onclick="triggerFileInput('topGarmentInput')">
                    <div class="upload-icon"><i class="fas fa-tshirt"></i></div>
                    <div class="upload-text">Subir prenda superior</div>
                    <input type="file" id="topGarmentInput" name="top_garment" accept=".jpg,.jpeg,.png" class="file-input">
                    <div class="preview" id="topPreview">
                        <img src="" alt="Preview">
                        <button class="btn btn-danger btn-sm remove-btn" onclick="clearFileInput('topGarmentInput', 'topUploadArea', 'topPreview')">
                            <i class="fas fa-times"></i>
                        </button>
                        <button class="btn btn-primary btn-sm reupload-btn" onclick="triggerFileInput('topGarmentInput')">
                            <i class="fas fa-upload"></i> Re-subir
                        </button>
                    </div>
                </div>

                <div class="upload" id="bottomUploadArea" onclick="triggerFileInput('bottomGarmentInput')">
                    <div class="upload-icon"><i class="fas fa-tshirt"></i></div>
                    <div class="upload-text">Subir prenda inferior</div>
                    <input type="file" id="bottomGarmentInput" name="bottom_garment" accept=".jpg,.jpeg,.png" class="file-input">
                    <div class="preview" id="bottomPreview">
                        <img src="" alt="Preview">
                        <button class="btn btn-danger btn-sm remove-btn" onclick="clearFileInput('bottomGarmentInput', 'bottomUploadArea', 'bottomPreview')">
                            <i class="fas fa-times"></i>
                        </button>
                        <button class="btn btn-primary btn-sm reupload-btn" onclick="triggerFileInput('bottomGarmentInput')">
                            <i class="fas fa-upload"></i> Re-subir
                        </button>
                    </div>
                </div>
            </div>

            <select class="form-control selector" name="output_count" disabled>
                <option value="1" selected>1 Salida (Fijo)</option>
            </select>

            <button class="btn btn-success btn-lg btn-block btn-generate mt-4" id="generateBtn">
                <i class="fas fa-magic"></i> Generar
            </button>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right-panel">
        <div class="results-header">
            <i class="fas fa-images"></i>
            <span><b>Probador Virtual Resultados</b></span>
        </div>
        <div class="results-content">
            <div id="resultsContainer">
                <!-- Results will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- TOOLTIP MODELO - OPTIMIZADO CON BOOTSTRAP 4 -->
<div class="tooltip model-guidelines" id="modelTooltip">
    <div class="tooltip-header bg-light border-bottom-dark d-flex justify-content-between align-items-center p-3">
        <h6 class="mb-0 text-dark d-flex align-items-center">
            <i class="fas fa-user mr-2"></i> Pautas de Modelos
        </h6>
        <button class="btn btn-outline-danger btn-sm" onclick="hideTooltip('modelTooltip')">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="tooltip-content p-3" style="max-height: 60vh; overflow-y: auto;">
        <p class="text-dark mb-3">
            <strong>Por favor siga estas pautas para subir imágenes de modelos y lograr los mejores resultados de Try-On.</strong>
        </p>
        <div class="alert alert-info py-2 px-3 mb-4">
            <strong>Imágenes de hasta 50MB, con lado corto ≥ 512px, lado largo ≤ 4096px y formatos JPG/PNG.</strong>
        </div>

        @if(count($validModels) > 0)
            <div class="row">
                @foreach ($validModels as $model)
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                        <div class="card border-dark shadow-sm h-100">
                            <div class="position-relative">
                                <img src="{{ $model['url'] }}" class="card-img-top" alt="{{ $model['description'] }}" style="height: 120px; object-fit: cover;">
                                <span class="badge badge-success position-absolute" style="top: 5px; right: 5px;">
                                    <i class="fas fa-check"></i>
                                </span>
                            </div>
                            <div class="card-body p-2 bg-secondary">
                                <p class="card-text text-light mb-0" style="font-size: 11px; line-height: 1.3;">
                                    {{ $model['description'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="alert alert-warning py-2 px-3 mb-3">
                <strong>Evite los malos ejemplos, ya que pueden reducir la calidad de los resultados de Try-On.</strong>
            </div>

            <div class="row">
                @foreach ($invalidModels as $model)
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                        <div class="card border-dark shadow-sm h-100">
                            <div class="position-relative">
                                <img src="{{ $model['url'] }}" class="card-img-top" alt="{{ $model['description'] }}" style="height: 120px; object-fit: cover;">
                                <span class="badge badge-danger position-absolute" style="top: 5px; right: 5px;">
                                    <i class="fas fa-times"></i>
                                </span>
                            </div>
                            <div class="card-body p-2 bg-secondary">
                                <p class="card-text text-light mb-0" style="font-size: 11px; line-height: 1.3;">
                                    {{ $model['description'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-info-circle fa-3x mb-3 text-muted"></i>
                <p class="text-muted">No hay directrices disponibles. Añada imágenes de ejemplo a las carpetas de directrices.</p>
            </div>
        @endif
    </div>
</div>

<!-- TOOLTIP PRENDA - OPTIMIZADO CON BOOTSTRAP 4 -->
<div class="tooltip model-guidelines" id="garmentTooltip">
    <div class="tooltip-header bg-light border-bottom-dark d-flex justify-content-between align-items-center p-3">
        <h6 class="mb-0 text-dark d-flex align-items-center">
            <i class="fas fa-tshirt mr-2"></i> Pautas de prendas
        </h6>
        <button class="btn btn-outline-danger btn-sm" onclick="hideTooltip('garmentTooltip')">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="tooltip-content p-3" style="max-height: 60vh; overflow-y: auto;">
        <p class="text-dark mb-3">
            <strong>Siga estas pautas para subir imágenes de prendas y lograr los mejores resultados de Try-On.</strong>
        </p>
        <div class="alert alert-info py-2 px-3 mb-4">
            <strong>Imágenes de hasta 50MB, con lado corto ≥ 512px, lado largo ≤ 4096px y formatos JPG/PNG.</strong>
        </div>

        @if(count($validGarments) > 0)
            <div class="row">
                @foreach ($validGarments as $garment)
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                        <div class="card border-dark shadow-sm h-100">
                            <div class="position-relative">
                                <img src="{{ $garment['url'] }}" class="card-img-top" alt="{{ $garment['description'] }}" style="height: 120px; object-fit: cover;">
                                <span class="badge badge-success position-absolute" style="top: 5px; right: 5px;">
                                    <i class="fas fa-check"></i>
                                </span>
                            </div>
                            <div class="card-body p-2 bg-secondary">
                                <p class="card-text text-light mb-0" style="font-size: 11px; line-height: 1.3;">
                                    {{ $garment['description'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="alert alert-warning py-2 px-3 mb-3">
                <strong>Evite los malos ejemplos, ya que pueden reducir la calidad de los resultados de Try-On.</strong>
            </div>

            <div class="row">
                @foreach ($invalidGarments as $garment)
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                        <div class="card border-dark shadow-sm h-100">
                            <div class="position-relative">
                                <img src="{{ $garment['url'] }}" class="card-img-top" alt="{{ $garment['description'] }}" style="height: 120px; object-fit: cover;">
                                <span class="badge badge-danger position-absolute" style="top: 5px; right: 5px;">
                                    <i class="fas fa-times"></i>
                                </span>
                            </div>
                            <div class="card-body p-2 bg-secondary">
                                <p class="card-text text-light mb-0" style="font-size: 11px; line-height: 1.3;">
                                    {{ $garment['description'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-info-circle fa-3x mb-3 text-muted"></i>
                <p class="text-muted">No hay directrices de prendas disponibles. Por favor, añada imágenes de ejemplo a las carpetas de directrices.</p>
            </div>
        @endif
    </div>
</div>

<!-- MODAL PARA VER RESULTADOS -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-images"></i> Vista previa de resultados
                </h5>
                <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal" aria-label="Close">
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
                                <button class="btn btn-outline-primary btn-sm ml-2" onclick="resetZoom()" title="Ajustar a pantalla">
                                    <i class="fas fa-expand-arrows-alt"></i> Ajustar
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
<!-- HIDDEN FORM -->
<form id="hiddenForm" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="model_source" id="hiddenModelSource">
    <input type="hidden" name="selected_default_model" id="hiddenSelectedModel">
    <input type="hidden" name="selected_virtual_model" id="hiddenSelectedVirtualModel">
    <input type="hidden" name="garment_type" id="hiddenGarmentType" value="single">
    <input type="hidden" name="output_count" id="hiddenOutputCount" value="1">
</form>

<script>
window.existingTryOns = @json($existingTryOns);
</script>
@endsection

@push('scripts')
<script>
let resultsHistory = [];
let currentZoom = 1;
let currentImageSrc = '';
let isDragging = false;
let startX, startY, translateX = 0, translateY = 0;

$(document).ready(function() {
    // INIT DATA
    if (window.existingTryOns?.length) {
        resultsHistory = window.existingTryOns.map(tryOn => ({
            id: tryOn.id,
            task_id: tryOn.task_id,
            model_type: tryOn.model_type,
            garments_type: tryOn.garments_type,
            status: tryOn.status === 'completed' ? 'completed' : 'processing',
            created_at: tryOn.created_at,
            result_image_paths: tryOn.result_image_paths?.map(path => `/storage/${path}`) || []
        }));
        renderResultsHistory();
    } else {
        showEmptyState();
    }

    // TOOLTIPS MEJORADOS
    $('#modelInfoBtn').click(e => {
        e.stopPropagation();
        hideTooltip('garmentTooltip');
        toggleTooltip('modelTooltip', e.target);
    });

    $('#garmentInfoBtn').click(e => {
        e.stopPropagation();
        hideTooltip('modelTooltip');
        toggleTooltip('garmentTooltip', e.target);
    });

    $(document).click(() => {
        hideTooltip('modelTooltip');
        hideTooltip('garmentTooltip');
    });

    // Prevenir cierre al hacer clic dentro del tooltip
    $('.tooltip').click(e => {
        e.stopPropagation();
    });

    // TABS CON BOOTSTRAP
    $('.sub-tab').click(function() {
        const tab = $(this).data('tab');
        $('.sub-tab').removeClass('active btn-secondary').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('active btn-secondary');
        $('.tab-content').addClass('d-none');
        $(`[data-content="${tab}"]`).removeClass('d-none');
        $('#hiddenModelSource').val(tab);
        $('.item').removeClass('selected');
        $('input[name^="selected_"]').prop('checked', false);
        $('#hiddenSelectedModel, #hiddenSelectedVirtualModel').val('');
    });

    // GARMENT TABS CON BOOTSTRAP BUTTON GROUP
    $('input[name="garment_type"]').change(function() {
        const garment = $(this).data('garment');
        $('.garment-content').addClass('d-none');
        $(`[data-garment-content="${garment}"]`).removeClass('d-none');
        $('#hiddenGarmentType').val(garment);
    });

    // MODEL SELECTION
    $(document).on('click', '.item', function() {
        $('.item').removeClass('selected');
        $(this).addClass('selected');

        const modelValue = $(this).data('model');
        const modelIndex = $(this).data('index');

        if ($(this).find('input[name="selected_default_model"]').length) {
            $(this).find('input[name="selected_default_model"]').prop('checked', true);
            $('#hiddenSelectedModel').val(modelValue);
            $('#hiddenSelectedVirtualModel').val('');
            $('#hiddenSelectedVirtualIndex').remove();
        } else {
            $(this).find('input[name="selected_virtual_model"]').prop('checked', true);
            $(this).find('input[name="selected_virtual_index"]').prop('checked', true);
            $('#hiddenSelectedVirtualModel').val(modelValue);

            if ($('#hiddenSelectedVirtualIndex').length === 0) {
                $('#hiddenForm').append(`<input type="hidden" name="selected_virtual_index" id="hiddenSelectedVirtualIndex" value="${modelIndex}">`);
            } else {
                $('#hiddenSelectedVirtualIndex').val(modelIndex);
            }
            $('#hiddenSelectedModel').val('');
        }
    });

    $('#hiddenOutputCount').val('1');

    // FILE HANDLERS
    const fileInputs = ['humanImageInput', 'singleGarmentInput', 'topGarmentInput', 'bottomGarmentInput'];
    const previews = ['humanPreview', 'singlePreview', 'topPreview', 'bottomPreview'];
    const areas = ['humanUploadArea', 'singleUploadArea', 'topUploadArea', 'bottomUploadArea'];

    fileInputs.forEach((input, i) => {
        $(`#${input}`).change(function() {
            if (this.files.length > 0) {
                showImagePreview(this.files[0], previews[i], areas[i]);
            }
        });
    });

    // 🧹 GENERATE SIMPLIFICADO (SOLO ERRORES CRÍTICOS)
    $('#generateBtn').click(function() {
        const modelSource = $('#hiddenModelSource').val() || 'default';
        const garmentType = $('#hiddenGarmentType').val();

        // 🛑 VALIDACIONES CRÍTICAS CON SWEETALERT2
        if (modelSource === 'default' && !$('#hiddenSelectedModel').val()) {
            Swal.fire({
                icon: 'warning',
                title: 'Modelo requerido',
                text: 'Por favor seleccione un modelo predeterminado',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        if (modelSource === 'virtual' && !$('#hiddenSelectedVirtualModel').val()) {
            Swal.fire({
                icon: 'warning',
                title: 'Modelo requerido',
                text: 'Por favor seleccione un modelo virtual',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        if (modelSource === 'upload' && !$('#humanImageInput')[0].files.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Imagen requerida',
                text: 'Por favor suba una imagen de modelo humano',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        if (garmentType === 'single' && !$('#singleGarmentInput')[0].files.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Prenda requerida',
                text: 'Por favor suba una prenda',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        if (garmentType === 'multiple' && (!$('#topGarmentInput')[0].files.length || !$('#bottomGarmentInput')[0].files.length)) {
            Swal.fire({
                icon: 'warning',
                title: 'Prendas requeridas',
                text: 'Por favor suba tanto la prenda superior como la inferior',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        const formData = new FormData();
        formData.append('_token', $('input[name="_token"]').val());
        formData.append('model_source', modelSource);
        formData.append('garment_type', garmentType);
        formData.append('output_count', '1');

        const modelActions = {
            default: () => formData.append('selected_default_model', $('#hiddenSelectedModel').val()),
            virtual: () => formData.append('selected_virtual_model', $('#hiddenSelectedVirtualModel').val()),
            upload: () => formData.append('human_image', $('#humanImageInput')[0].files[0])
        };
        modelActions[modelSource]();

        if (garmentType === 'single') {
            formData.append('single_garment', $('#singleGarmentInput')[0].files[0]);
        } else {
            formData.append('top_garment', $('#topGarmentInput')[0].files[0]);
            formData.append('bottom_garment', $('#bottomGarmentInput')[0].files[0]);
        }

        $(this).prop('disabled', true);

        const tempResult = {
            id: 'temp_' + Date.now(),
            model_type: modelSource,
            garments_type: garmentType,
            status: 'processing',
            created_at: new Date().toISOString(),
            task_id: null,
            result_image_paths: []
        };
        addResultToHistory(tempResult);

        $.ajax({
            url: "{{ route('virtual-try-on.generate') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: response => {
                if (response.data?.task_id) {
                    tempResult.task_id = response.data.task_id;
                    updateResultInHistory(tempResult);
                    checkTaskStatus(response.data.task_id);
                } else {
                    tempResult.status = 'failed';
                    updateResultInHistory(tempResult);
                    resetGenerateButton();
                }
            },
            error: (xhr) => {
                Swal.close();
                tempResult.status = 'failed';
                updateResultInHistory(tempResult);
                resetGenerateButton();

                // 🍯 ERROR DETALLADO CON SWEETALERT2
                let errorMessage = 'Error desconocido';
                if (xhr.responseJSON?.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON?.error) {
                    errorMessage = xhr.responseJSON.error;
                } else if (xhr.responseText) {
                    errorMessage = 'Error del servidor';
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error de generación',
                    text: errorMessage,
                    confirmButtonText: 'Reintentar',
                });
            }
        });
    });
    $('#hiddenModelSource').val('default');
});

// FUNCIONES TOOLTIP MEJORADAS
const toggleTooltip = (tooltipId, targetElement) => {
    const tooltip = document.getElementById(tooltipId);
    const rect = targetElement.getBoundingClientRect();
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;

    if (tooltip.style.display === 'block') {
        hideTooltip(tooltipId);
    } else {
        tooltip.style.display = 'block';

        let left = rect.left - 450;
        if (left < 10) left = 10;
        if (left + 1000 > viewportWidth) left = viewportWidth - 1010;

        let top = rect.bottom + 10;
        if (top + 500 > viewportHeight) top = rect.top - 510;
        if (top < 10) top = 10;

        tooltip.style.left = left + 'px';
        tooltip.style.top = top + 'px';

        tooltip.style.opacity = '0';
        tooltip.style.transform = 'scale(0.95)';

        setTimeout(() => {
            tooltip.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
            tooltip.style.opacity = '1';
            tooltip.style.transform = 'scale(1)';
        }, 10);
    }
};

const hideTooltip = tooltipId => {
    const tooltip = document.getElementById(tooltipId);
    if (tooltip) {
        tooltip.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
        tooltip.style.opacity = '0';
        tooltip.style.transform = 'scale(0.95)';

        setTimeout(() => {
            tooltip.style.display = 'none';
        }, 200);
    }
};

// FUNCIONES DE MODAL Y ZOOM
const openImageModal = (imageSrc, title) => {
    currentImageSrc = imageSrc;
    currentZoom = 1;
    translateX = 0;
    translateY = 0;
    $('#modalImage').attr('src', imageSrc);
    $('.modal-title').html(`<i class="fas fa-images"></i> ${title}`);
    $('#zoomLevel').text('100%');
    updateImageTransform();
    $('#imageModal').modal('show');
};

// 🔍 ZOOM CENTRADO EN EL CURSOR
const zoomImage = (factor) => {
    currentZoom *= factor;
    currentZoom = Math.max(0.5, Math.min(currentZoom, 5));
    updateImageTransform();
    $('#zoomLevel').text(Math.round(currentZoom * 100) + '%');
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
};

const resetZoom = () => {
    currentZoom = 1;
    translateX = 0;
    translateY = 0;
    updateImageTransform();
    $('#zoomLevel').text('100%');
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

// EVENTOS DE DRAG Y ZOOM
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

// FUNCIONES DE ARCHIVOS
const triggerFileInput = inputId => {
    event.stopPropagation();
    document.getElementById(inputId).click();
};

const showImagePreview = (file, previewId, uploadAreaId) => {
    const reader = new FileReader();
    reader.onload = e => {
        const preview = document.getElementById(previewId);
        const uploadArea = document.getElementById(uploadAreaId);
        preview.querySelector('img').src = e.target.result;
        preview.style.display = 'block';
        uploadArea.classList.add('has-file');
        uploadArea.querySelector('.upload-icon').style.display = 'none';
        uploadArea.querySelector('.upload-text').style.display = 'none';
    };
    reader.readAsDataURL(file);
};

const clearFileInput = (inputId, uploadAreaId, previewId) => {
    event.stopPropagation();

    const input = document.getElementById(inputId);
    const uploadArea = document.getElementById(uploadAreaId);
    const preview = document.getElementById(previewId);
    input.value = '';
    preview.style.display = 'none';
    uploadArea.classList.remove('has-file');
    uploadArea.querySelector('.upload-icon').style.display = 'block';
    uploadArea.querySelector('.upload-text').style.display = 'block';
};

// FUNCIONES DE ESTADO
const showEmptyState = () => {
    $('#resultsContainer').html(`
        <div class="text-center">
            <i class="fas fa-tshirt fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Aún no hay resultados</h5>
            <p class="text-muted">Complete el formulario para generar su primer probador</p>
        </div>
    `);
};

const addResultToHistory = result => {
    resultsHistory.unshift(result);
    renderResultsHistory();
};

const updateResultInHistory = updatedResult => {
    const index = resultsHistory.findIndex(r => r.id === updatedResult.id || r.task_id === updatedResult.task_id);
    if (index !== -1) {
        resultsHistory[index] = { ...resultsHistory[index], ...updatedResult };
        renderResultsHistory();
    }
};

const renderResultsHistory = () => {
    const container = $('#resultsContainer');
    if (resultsHistory.length === 0) return showEmptyState();

    let html = '';
    resultsHistory.forEach((result, index) => {
        const statusClass = `status ${result.status}`;
        const date = new Date(result.created_at).toLocaleString();

        let modelTypeText = result.model_type;
        if (result.model_type === 'default') {
            modelTypeText = 'Predeterminado';
        } else if (result.model_type === 'virtual') {
            modelTypeText = 'Virtual';
        } else if (result.model_type === 'upload') {
            modelTypeText = 'Subido';
        }

        let garmentTypeText = result.garments_type;
        if (result.garments_type === 'single') {
            garmentTypeText = 'Prenda Única';
        } else if (result.garments_type === 'multiple') {
            garmentTypeText = 'Múltiples Prendas';
        }

        let statusText = result.status;
        if (result.status === 'processing') {
            statusText = 'En Proceso';
        } else if (result.status === 'completed') {
            statusText = 'Completado';
        } else if (result.status === 'failed') {
            statusText = 'Fallido';
        }

        html += `
            <div class="result-group">
                <div class="result-header">
                    <div class="result-info">
                        <div><h6>Probador Virtual Try-On - Resultados</h6></div>
                        <div><b>Fecha y hora de creación: </b>${date}</div>
                        <div><b>Modelo: </b>${modelTypeText} | <b>Prenda/s: </b>${garmentTypeText}</div>
                    </div>
                    <div class="${statusClass}">${statusText.toUpperCase()}</div>
                </div>
                <div class="grid-4">`;

        if (result.result_image_paths?.length) {
            result.result_image_paths.forEach((path, imgIndex) => {
                html += `<div class="item" onclick="openImageModal('${path}', 'Resultado ${imgIndex + 1}')">
                    <img src="${path}" alt="Resultado ${imgIndex + 1}">
                    <div class="image-overlay">
                        <i class="fas fa-search-plus"></i>
                    </div>
                </div>`;
            });
        } else if (result.status === 'processing') {
            html += `<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--txt-2);">
                <div class="spinner-border text-primary" role="status"></div>
                <p style="margin-top: 16px;">Procesando...</p>
                <small>⏳ Tiempo estimado: 5–20 segundos.</small>
            </div>`;
        } else {
            html += `<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--txt-2);">
                <i class="fas fa-exclamation-circle" style="font-size: 32px; margin-bottom: 16px; opacity: 0.5;"></i>
                <p>No hay resultados disponibles</p>
            </div>`;
        }

        html += `</div></div>`;
    });

    container.html(html);

    $('.item').hover(
        function() { $(this).find('.image-overlay').css('opacity', '1'); },
        function() { $(this).find('.image-overlay').css('opacity', '0'); }
    );
};

// FUNCIONES DE MONITOREO
const checkTaskStatus = taskId => {
    setTimeout(function poll() {
        $.get(`/virtual-try-on/status/${taskId}`)
            .done(response => {
                if (response.data) {
                    const status = response.data.task_status;
                    if (status === 'succeed') {
                        const result = resultsHistory.find(r => r.task_id === taskId);
                        if (result) {
                            result.status = 'completed';
                            result.result_image_paths = response.data.local_images || [];
                            updateResultInHistory(result);
                        }
                        resetGenerateButton();
                    } else if (status === 'processing' || status === 'submitted') {
                        setTimeout(poll, 3000);
                    } else {
                        const result = resultsHistory.find(r => r.task_id === taskId);
                        if (result) {
                            result.status = 'failed';
                            updateResultInHistory(result);
                        }
                        resetGenerateButton();
                    }
                }
            })
            .fail(() => {
                const result = resultsHistory.find(r => r.task_id === taskId);
                if (result) {
                    result.status = 'failed';
                    updateResultInHistory(result);
                }
                resetGenerateButton();
            });
    }, 2000);
};

const resetGenerateButton = () => {
    $('#generateBtn').prop('disabled', false);
};
</script>
@endpush
