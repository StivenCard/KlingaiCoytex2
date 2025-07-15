@extends('layouts.app')

@section('content')
<div class="main-container">
    <!-- LEFT PANEL -->
    <div class="left-panel">
        <!-- SCROLL CONTAINER -->
        <div class="scroll-container">
            <!-- MODEL SETTINGS -->
            <div class="section">
                <h6 class="title"><i class="fas fa-cog"></i> Configuración del Modelo</h6>

                <!-- GENDER -->
                <div class="group">
                    <label class="label">Género</label>
                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                        <label class="btn btn-outline-primary active">
                            <input type="radio" name="gender" id="male" autocomplete="off" checked data-gender="male">
                            <i class="fas fa-mars"></i> Masculino
                        </label>
                        <label class="btn btn-outline-primary">
                            <input type="radio" name="gender" id="female" autocomplete="off" data-gender="female">
                            <i class="fas fa-venus"></i> Femenino
                        </label>
                    </div>
                </div>

                <!-- AGE -->
                <div class="group">
                    <label class="label">Edad</label>
                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                        <label class="btn btn-outline-secondary">
                            <input type="radio" name="age" id="children" autocomplete="off" data-age="children">
                            <i class="fas fa-child"></i> Niños
                        </label>
                        <label class="btn btn-outline-secondary active">
                            <input type="radio" name="age" id="youth" autocomplete="off" checked data-age="youth">
                            <i class="fas fa-user"></i> Jóvenes
                        </label>
                        <label class="btn btn-outline-secondary">
                            <input type="radio" name="age" id="elderly" autocomplete="off" data-age="elderly">
                            <i class="fas fa-user-tie"></i> Adultos
                        </label>
                    </div>
                </div>

                <!-- SKIN TONE -->
                <div class="group">
                    <label class="label">Tono de Piel</label>
                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                        <label class="btn btn-outline-secondary active color-btn" style="background: rgb(255, 241, 228);">
                            <input type="radio" name="skin_tone" id="light" autocomplete="off" checked data-skin="light">
                            <span class="color-check"><i class="fas fa-check"></i></span>
                        </label>
                        <label class="btn btn-outline-secondary color-btn" style="background: rgb(250, 201, 145);">
                            <input type="radio" name="skin_tone" id="medium" autocomplete="off" data-skin="medium">
                            <span class="color-check"><i class="fas fa-check"></i></span>
                        </label>
                        <label class="btn btn-outline-secondary color-btn" style="background: rgb(143, 81, 40);">
                            <input type="radio" name="skin_tone" id="dark" autocomplete="off" data-skin="dark">
                            <span class="color-check"><i class="fas fa-check"></i></span>
                        </label>
                        <label class="btn btn-outline-secondary color-btn" style="background: rgb(195, 151, 112);">
                            <input type="radio" name="skin_tone" id="olive" autocomplete="off" data-skin="olive">
                            <span class="color-check"><i class="fas fa-check"></i></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- PROMPT SECTION -->
            <div class="section">
                <h6 class="title"><i class="fas fa-edit"></i> Prompt Personalizado (Opcional)</h6>

                <!-- HINTS -->
                <div class="group">
                    <label class="label">Sugerencias Rápidas</label>
                    <div class="row">
                        @foreach($hints as $hint)
                            <div class="col-6 mb-2">
                                <button class="btn btn-outline-info btn-sm btn-block hint-btn" data-hint="{{ $hint['key'] }}" data-prompt="{{ $hint['prompt'] }}">
                                    <i class="fas fa-{{ $hint['key'] === 'elegante' ? 'gem' : ($hint['key'] === 'urbano' ? 'city' : ($hint['key'] === 'energetico' ? 'bolt' : ($hint['key'] === 'dulce' ? 'heart' : 'glasses'))) }}"></i>
                                    {{ $hint['name'] }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- TEXTAREA -->
                <div class="group">
                    <div class="form-group">
                        <textarea id="promptText" class="form-control" rows="4" placeholder="Ingresa un prompt personalizado o selecciona una sugerencia arriba..." maxlength="2500"></textarea>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted char-count">0/2500</small>
                            <button class="btn btn-outline-secondary btn-sm" id="clearPrompt">
                                <i class="fas fa-eraser"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- OUTPUT SETTINGS -->
            <div class="section">
                <h6 class="title"><i class="fas fa-cogs"></i> Configuración de Salida</h6>

                <!-- ASPECT RATIO -->
                <div class="group">
                    <label class="label">Relación de Aspecto</label>
                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                        <label class="btn btn-outline-warning active">
                            <input type="radio" name="aspect_ratio" id="ratio-3-4" autocomplete="off" checked data-ratio="3:4">3:4
                        </label>
                        <label class="btn btn-outline-warning">
                            <input type="radio" name="aspect_ratio" id="ratio-2-3" autocomplete="off" data-ratio="2:3">2:3
                        </label>
                        <label class="btn btn-outline-warning">
                            <input type="radio" name="aspect_ratio" id="ratio-9-16" autocomplete="off" data-ratio="9:16">9:16
                        </label>
                        <label class="btn btn-outline-warning">
                            <input type="radio" name="aspect_ratio" id="ratio-1-1" autocomplete="off" data-ratio="1:1">1:1
                        </label>
                    </div>
                </div>

                <!-- OUTPUT COUNT BLOQUEADO -->
                <div class="group">
                    <label class="label">Cantidad de Imágenes</label>
                    <select class="form-control" id="outputCount" disabled>
                        <option value="1" selected>1 Resultado (Fijo)</option>
                    </select>
                </div>

                <!-- GENERATE -->
                <button class="btn btn-success btn-lg btn-block" id="generateVirtualModel">
                    <i class="fas fa-user-plus"></i> Generar Modelo Virtual
                </button>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right-panel">
        <div class="results-header">
            <i class="fas fa-users"></i>
            <span>Modelos Virtuales Generados</span>
        </div>
        <div class="results-content">
            <div id="virtualModelResults">
                <!-- Results will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- MODAL PARA VER VIRTUAL MODELS -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user"></i> Vista Previa del Modelo Virtual
                </h5>
                <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body p-0">
                <!-- IMAGE CONTAINER -->
                <div class="image-viewer-container">
                    <img id="modalImage" src="" alt="Virtual Model" class="vm-modal-image">
                </div>

                <!-- CONTROLS -->
                <div class="image-controls">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <!-- ZOOM CONTROLS -->
                            <div class="zoom-controls">
                                <button class="btn btn-primary btn-sm" onclick="zoomImage(0.9)">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                                <span class="badge badge-secondary mx-2" id="zoomLevel">100%</span>
                                <button class="btn btn-primary btn-sm" onclick="zoomImage(1.1)">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                                <button class="btn btn-outline-primary btn-sm ml-2" onclick="resetZoom()">
                                    <i class="fas fa-expand-arrows-alt"></i> Restablecer
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 text-right">
                            <!-- DOWNLOAD BUTTON -->
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
<form id="virtualModelForm">
    @csrf
    <input type="hidden" name="gender" id="hiddenGender" value="male">
    <input type="hidden" name="age_group" id="hiddenAge" value="youth">
    <input type="hidden" name="skin_tone" id="hiddenSkin" value="light">
    <input type="hidden" name="aspect_ratio" id="hiddenRatio" value="3:4">
    <input type="hidden" name="output_count" id="hiddenOutputCount" value="1">
    <input type="hidden" name="prompt" id="hiddenPrompt">
</form>

<script>
window.existingModels = @json($existingModels);
</script>
@endsection

@push('scripts')
<script>
let modelHistory = [];
let selectedHint = null;
let isPromptManuallyEdited = false;

// VARIABLES PARA ZOOM
let currentZoom = 1;
let currentImageSrc = '';
let isDragging = false;
let startX, startY, translateX = 0, translateY = 0;

$(document).ready(function() {
    initializeExistingData();
    initializeBootstrapSelectors();
    initializeHints();
    initializePrompt();
    initializeGeneration();
    updateCharCount();
});

function initializeExistingData() {
    if (window.existingModels?.length) {
        modelHistory = window.existingModels.map(model => ({
            id: model.id,
            task_id: model.task_id,
            gender: model.gender,
            age_group: model.age_group,
            skin_tone: model.skin_tone,
            aspect_ratio: model.aspect_ratio,
            prompt: model.prompt,
            status: model.status,
            created_at: model.created_at,
            result_image_paths: model.result_image_paths || [],
            display_name: model.display_name,
            formatted_date: model.formatted_date,
            preview_url: model.preview_url
        }));
        renderModelHistory();
    } else {
        showEmptyState();
    }
}

// SELECTORES CON BOOTSTRAP BUTTON GROUPS
function initializeBootstrapSelectors() {
    // Gender selection
    $('input[name="gender"]').change(function() {
        $('#hiddenGender').val($(this).data('gender'));
    });

    // Age selection
    $('input[name="age"]').change(function() {
        $('#hiddenAge').val($(this).data('age'));
    });

    // Skin tone selection
    $('input[name="skin_tone"]').change(function() {
        $('#hiddenSkin').val($(this).data('skin'));
    });

    // Aspect ratio selection
    $('input[name="aspect_ratio"]').change(function() {
        $('#hiddenRatio').val($(this).data('ratio'));
    });

    $('#hiddenOutputCount').val('1');
}

function initializeHints() {
    $('.hint-btn').click(function() {
        const hint = $(this).data('hint');
        const hintPrompt = $(this).data('prompt');

        if (selectedHint === hint) {
            $(this).removeClass('btn-info').addClass('btn-outline-info');
            selectedHint = null;
            if (!isPromptManuallyEdited) {
                $('#promptText').val('');
                updateCharCount();
            }
        } else {
            $('.hint-btn').removeClass('btn-info').addClass('btn-outline-info');
            $(this).removeClass('btn-outline-info').addClass('btn-info');
            selectedHint = hint;
            if (!isPromptManuallyEdited) {
                $('#promptText').val(hintPrompt);
                updateCharCount();
            }
        }
    });
}

function initializePrompt() {
    $('#promptText').on('input', function() {
        const value = $(this).val().trim();
        updateCharCount();
        isPromptManuallyEdited = value.length > 0;
        $('#hiddenPrompt').val(value);
    });

    $('#clearPrompt').click(function() {
        $('#promptText').val('');
        $('#hiddenPrompt').val('');
        $('.hint-btn').removeClass('btn-info').addClass('btn-outline-info');
        selectedHint = null;
        isPromptManuallyEdited = false;
        updateCharCount();
    });
}

function initializeGeneration() {
    $('#generateVirtualModel').click(function() {
        const prompt = $('#promptText').val().trim();
        if (prompt.length > 0) $('#hiddenPrompt').val(prompt);

        const formData = prepareFormData();
        submitGeneration(formData);
    });
}

function prepareFormData() {
    const formData = new FormData();
    const fields = ['_token', 'gender', 'age_group', 'skin_tone', 'aspect_ratio', 'output_count', 'prompt'];

    fields.forEach(field => {
        if (field === '_token') {
            formData.append(field, $('input[name="_token"]').val());
        } else {
            const hiddenField = `#hidden${field.replace(/_([a-z])/g, (_, letter) => letter.toUpperCase()).replace(/^\w/, c => c.toUpperCase())}`;
            formData.append(field, $(hiddenField).val());
        }
    });

    return formData;
}

function submitGeneration(formData) {
    $('#generateVirtualModel').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generando...');

    const tempResult = {
        id: 'temp_' + Date.now(),
        gender: $('#hiddenGender').val(),
        age_group: $('#hiddenAge').val(),
        skin_tone: $('#hiddenSkin').val(),
        aspect_ratio: $('#hiddenRatio').val(),
        prompt: $('#hiddenPrompt').val(),
        status: 'processing',
        created_at: new Date().toISOString(),
        task_id: null,
        result_image_paths: []
    };
    addModelToHistory(tempResult);

    $.ajax({
        url: "{{ route('virtual-model.generate') }}",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: response => {
            if (response.data?.task_id) {
                tempResult.task_id = response.data.task_id;
                updateModelInHistory(tempResult);
                checkModelStatus(response.data.task_id);
            } else {
                tempResult.status = 'failed';
                updateModelInHistory(tempResult);
                resetGenerateButton();
            }
        },
        error: () => {
            tempResult.status = 'failed';
            updateModelInHistory(tempResult);
            resetGenerateButton();
        }
    });
}

// FUNCIONES DE ZOOM Y MODAL
const openImageModal = (imageSrc, title) => {
    currentImageSrc = imageSrc;
    currentZoom = 1;
    translateX = 0;
    translateY = 0;

    $('#modalImage').attr('src', imageSrc);
    $('.modal-title').html(`<i class="fas fa-user"></i> ${title || 'Vista Previa del Modelo Virtual'}`);
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
    link.download = `virtual-model-${Date.now()}.jpg`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

// EVENTOS DE DRAG
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

// UTILIDADES
const updateCharCount = () => {
    const length = $('#promptText').val().length;
    $('.char-count').text(length + '/2500');
};

const addModelToHistory = model => {
    modelHistory.unshift(model);
    renderModelHistory();
};

const updateModelInHistory = updatedModel => {
    const index = modelHistory.findIndex(m => m.id === updatedModel.id || m.task_id === updatedModel.task_id);
    if (index !== -1) {
        modelHistory[index] = { ...modelHistory[index], ...updatedModel };
        renderModelHistory();
    }
};

const renderModelHistory = () => {
    const container = $('#virtualModelResults');
    if (modelHistory.length === 0) return showEmptyState();

    let html = '';
    modelHistory.forEach(model => {
        const statusClass = `status ${model.status}`;
        const date = model.formatted_date || new Date(model.created_at).toLocaleString('es-ES');

        html += `
            <div class="result-group">
                <div class="result-header">
                    <div class="result-info">
                        <div><strong>${model.display_name || 'Modelo Virtual ' + (model.task_id || 'Pendiente')}</strong></div>
                        <div>${date}</div>
                        <div>Género: ${model.gender} | Edad: ${model.age_group} | Piel: ${model.skin_tone}</div>
                        ${model.prompt ? `<div class="prompt-used"><strong>Prompt:</strong> ${model.prompt.substring(0, 100)}${model.prompt.length > 100 ? '...' : ''}</div>` : ''}
                    </div>
                    <div class="${statusClass}">${model.status.toUpperCase()}</div>
                </div>
                <div class="grid-4">`;

        if (model.result_image_paths?.length) {
            model.result_image_paths.forEach((path, imgIndex) => {
                const imageSrc = (model.preview_url && imgIndex === 0) ? model.preview_url :
                    (path.startsWith('/storage/') ? path : `/storage/${path}`);
                html += `<div class="item" onclick="openImageModal('${imageSrc}', 'Modelo Virtual ${imgIndex + 1}')">
                    <img src="${imageSrc}" alt="Modelo ${imgIndex + 1}">
                    <div class="image-overlay">
                        <i class="fas fa-search-plus"></i>
                    </div>
                </div>`;
            });
        } else if (model.status === 'processing') {
            html += `<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--txt-2);">
                <div class="spinner-border text-primary" role="status"></div>
                <p style="margin-top: 16px;">Generando modelo virtual...</p>
            </div>`;
        } else {
            html += `<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--txt-2);">
                <i class="fas fa-exclamation-circle" style="font-size: 32px; margin-bottom: 16px; opacity: 0.5;"></i>
                <p>Falló la generación</p>
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

const showEmptyState = () => {
    $('#virtualModelResults').html(`
        <div class="text-center">
            <i class="fas fa-user-plus"></i>
            <p>Aún no hay modelos virtuales</p>
            <small>Configura los ajustes y genera tu primer modelo virtual</small>
        </div>
    `);
};

const checkModelStatus = taskId => {
    setTimeout(function poll() {
        $.get(`/virtual-model/status/${taskId}`)
            .done(response => {
                if (response.data) {
                    const status = response.data.task_status;
                    if (status === 'succeed') {
                        const model = modelHistory.find(m => m.task_id === taskId);
                        if (model) {
                            model.status = 'completed';
                            model.result_image_paths = response.data.local_images || [];
                            updateModelInHistory(model);
                        }
                        resetGenerateButton();
                    } else if (status === 'processing' || status === 'submitted') {
                        setTimeout(poll, 3000);
                    } else {
                        const model = modelHistory.find(m => m.task_id === taskId);
                        if (model) {
                            model.status = 'failed';
                            updateModelInHistory(model);
                        }
                        resetGenerateButton();
                    }
                }
            })
            .fail(() => {
                const model = modelHistory.find(m => m.task_id === taskId);
                if (model) {
                    model.status = 'failed';
                    updateModelInHistory(model);
                }
                resetGenerateButton();
            });
    }, 2000);
};

const resetGenerateButton = () => {
    $('#generateVirtualModel').prop('disabled', false).html('<i class="fas fa-user-plus"></i> Generar Modelo Virtual');
};
</script>
@endpush
