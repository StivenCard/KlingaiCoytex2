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

<!-- TOOLTIP MODELO - DISEÑO TIPO CARDS -->
<div class="tooltip model-guidelines" id="modelTooltip">
    <div class="tooltip-header">
        <h6><i class="fas fa-user"></i> Pautas de Modelos</h6>
        <button class="btn btn-outline-danger" onclick="hideTooltip('modelTooltip')">
            x
        </button>
    </div>
    <div class="tooltip-content">
        <p><strong>Por favor sigue estas pautas para subir imágenes de modelos y lograr los mejores resultados de Try-On.</strong></p>
        <p class="mb-3">Imágenes de hasta 50MB, con lado corto ≥ 512px, lado largo ≤ 4096px y formatos JPG/PNG.</p>

        @if(count($validModels) > 0)
            <div class="row">
                @foreach ($validModels as $model)
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                        <div class="card">
                            <img src="{{ $model['url'] }}" class="card-img-top" alt="{{ $model['description'] }}">
                            <span class="badge badge-success"><i class="fas fa-check"></i></span>
                            <div class="card-body">
                                <p class="mb-1">{{ $model['description'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <p><strong>Evite los malos ejemplos, ya que pueden reducir la calidad de los resultados de Try-On.</strong></p>
            <div class="row">
                @foreach ($invalidModels as $model)
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                        <div class="card">
                            <img src="{{ $model['url'] }}" class="card-img-top" alt="{{ $model['description'] }}">
                            <span class="badge badge-danger"><i class="fas fa-times"></i></span>
                            <div class="card-body">
                                <p class="mb-1">{{ $model['description'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-info-circle fa-3x mb-3" style="color: #666;"></i>
                <p>No hay directrices disponibles. Añada imágenes de ejemplo a las carpetas de directrices.</p>
            </div>
        @endif
    </div>
</div>

<!-- TOOLTIP PRENDA - DISEÑO TIPO GRID -->
<div class="tooltip garment-guidelines" id="garmentTooltip">
    <div class="tooltip-header">
        <h6><i class="fas fa-tshirt"></i> Pautas de prendas</h6>
        <button class="tooltip-close" onclick="hideTooltip('garmentTooltip')">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="tooltip-content">
        <div class="tooltip-specs">
            <p><strong>Requisitos:</strong></p>
            <ul>
                <li>Tamaño: Hasta 50MB</li>
                <li>Resolución: Lado corto ≥512px, Lado largo ≤4096px</li>
                <li>Formatos: JPG/PNG</li>
            </ul>
        </div>

        @if(count($validGarments) > 0)
            <div class="guidelines-section">
                <h6 class="section-title valid">✓ Siga estas pautas</h6>
                <div class="grid-6">
                    @foreach($validGarments as $garment)
                        <div class="guideline-item">
                            <div class="guideline-image">
                                <img src="{{ $garment['url'] }}" alt="{{ $garment['description'] }}">
                            </div>
                            <div class="guideline-label valid">{{ $garment['description'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="guidelines-section">
                <h6 class="section-title invalid">✗ Evite estos ejemplos</h6>
                <div class="grid-6">
                    @foreach($invalidGarments as $garment)
                        <div class="guideline-item">
                            <div class="guideline-image">
                                <img src="{{ $garment['url'] }}" alt="{{ $garment['description'] }}">
                            </div>
                            <div class="guideline-label invalid">{{ $garment['description'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-info-circle fa-3x mb-3" style="color: #666;"></i>
                <p>No hay directrices de prendas disponibles. Por favor, añada imágenes de ejemplo a las carpetas de directrices.</p>
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
                </div>
                <div class="image-controls">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="zoom-controls">
                                <button class="btn btn-primary btn-sm" onclick="zoomImage(0.9)">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                                <span id="zoomLevel" class="mx-2 badge badge-secondary">100%</span>
                                <button class="btn btn-primary btn-sm" onclick="zoomImage(1.1)">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                                <button class="btn btn-outline-primary btn-sm ml-2" onclick="resetZoom()">
                                    <i class="fas fa-expand-arrows-alt"></i> Reiniciar
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 text-right">
                            <button class="btn btn-success" onclick="downloadImage()">
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

    // GENERATE
    $('#generateBtn').click(function() {
        const modelSource = $('#hiddenModelSource').val() || 'default';
        const garmentType = $('#hiddenGarmentType').val();

        // 🌐 VALIDACIONES TRADUCIDAS AL ESPAÑOL
        const validations = {
            default: () => !$('#hiddenSelectedModel').val() && alert('Por favor seleccione un modelo predeterminado'),
            virtual: () => !$('#hiddenSelectedVirtualModel').val() && alert('Por favor seleccione un modelo virtual'),
            upload: () => !$('#humanImageInput')[0].files.length && alert('Por favor suba una imagen de modelo humano')
        };

        if (validations[modelSource]?.()) return;

        if (garmentType === 'single' && !$('#singleGarmentInput')[0].files.length) {
            return alert('Por favor suba una prenda');
        }

        if (garmentType === 'multiple' && (!$('#topGarmentInput')[0].files.length || !$('#bottomGarmentInput')[0].files.length)) {
            return alert('Por favor suba tanto la prenda superior como la inferior');
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

        // 🔄 MANTENER BOTÓN IGUAL - NO CAMBIAR A "Generating..."
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
            error: () => {
                tempResult.status = 'failed';
                updateResultInHistory(tempResult);
                resetGenerateButton();
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

        // Posicionamiento inteligente
        let left = rect.left - 450;
        if (left < 10) left = 10;
        if (left + 1000 > viewportWidth) left = viewportWidth - 1010;

        let top = rect.bottom + 10;
        if (top + 500 > viewportHeight) top = rect.top - 510;
        if (top < 10) top = 10;

        tooltip.style.left = left + 'px';
        tooltip.style.top = top + 'px';

        // Animación suave
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

const zoomImage = (factor) => {
    currentZoom *= factor;
    currentZoom = Math.max(0.5, Math.min(currentZoom, 5));
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
        transition: 'transform 0.1s ease-out',
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
    if (currentZoom <= 1) return;
    isDragging = true;
    startX = e.clientX - translateX;
    startY = e.clientY - translateY;
    $(this).css('cursor', 'grabbing');
});

$(document).on('mousemove', function(e) {
    if (!isDragging) return;
    translateX = e.clientX - startX;
    translateY = e.clientY - startY;
    updateImageTransform();
});

$(document).on('mouseup', function() {
    isDragging = false;
    $('#modalImage').css('cursor', 'grab');
});

$('#modalImage').on('wheel', function(e) {
    e.preventDefault();
    const factor = e.originalEvent.deltaY > 0 ? 0.9 : 1.1;
    zoomImage(factor);
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

        // Traducir tipos de modelo
        let modelTypeText = result.model_type;
        if (result.model_type === 'default') {
            modelTypeText = 'Predeterminado';
        } else if (result.model_type === 'virtual') {
            modelTypeText = 'Virtual';
        } else if (result.model_type === 'upload') {
            modelTypeText = 'Subido';
        }

        // Traducir tipos de prenda
        let garmentTypeText = result.garments_type;
        if (result.garments_type === 'single') {
            garmentTypeText = 'Prenda Única';
        } else if (result.garments_type === 'multiple') {
            garmentTypeText = 'Múltiples Prendas';
        }

        // Traducir estado
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
            // 🔄 ESTADO DE PROCESAMIENTO RESTAURADO
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
