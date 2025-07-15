@extends('layouts.app')

@section('content')
<div class="main-container">
    <!-- LEFT PANEL -->
    <div class="left-panel">
        <!-- TÍTULO -->
        <span class="d-block m-2"><b>Configuración del Modelo Virtual</b></span>

        <!-- SCROLL CONTAINER -->
        <div class="scroll-container">
            <!-- MODEL SETTINGS -->
            <div class="section">
                <div class="title d-flex align-items-center">
                    <i class="fas fa-cog mr-2"></i>
                    <span>Configuración del Modelo</span>
                    <button class="btn btn-outline-info btn-sm info-btn ml-auto" id="settingsInfoBtn" title="Guía de Configuración">
                        <i class="fas fa-info-circle"></i>
                    </button>
                </div>

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
                    <div class="d-flex justify-content-between">
                        <div class="color-btn active" data-skin="light" style="background: rgb(255, 241, 228);">
                            <input type="radio" name="skin_tone" id="light" checked data-skin="light" class="d-none">
                            <span class="color-check"><i class="fas fa-check"></i></span>
                        </div>
                        <div class="color-btn" data-skin="medium" style="background: rgb(250, 201, 145);">
                            <input type="radio" name="skin_tone" id="medium" data-skin="medium" class="d-none">
                            <span class="color-check"><i class="fas fa-check"></i></span>
                        </div>
                        <div class="color-btn" data-skin="dark" style="background: rgb(143, 81, 40);">
                            <input type="radio" name="skin_tone" id="dark" data-skin="dark" class="d-none">
                            <span class="color-check"><i class="fas fa-check"></i></span>
                        </div>
                        <div class="color-btn" data-skin="olive" style="background: rgb(195, 151, 112);">
                            <input type="radio" name="skin_tone" id="olive" data-skin="olive" class="d-none">
                            <span class="color-check"><i class="fas fa-check"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PROMPT SECTION -->
            <div class="section">
                <div class="title d-flex align-items-center">
                    <i class="fas fa-edit mr-2"></i>
                    <span>Prompt Personalizado</span>
                    <button class="btn btn-outline-info btn-sm info-btn ml-auto" id="promptInfoBtn" title="Guía de Prompts">
                        <i class="fas fa-info-circle"></i>
                    </button>
                </div>

                <!-- HINTS MEJORADOS -->
                <div class="group">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="label mb-0">Sugerencias Rápidas</label>
                        <button class="btn btn-outline-secondary btn-sm" id="refreshHints" title="Cargar nuevas sugerencias">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="grid-4" id="hintsContainer">
                        <!-- Los hints se cargarán aquí dinámicamente -->
                    </div>
                </div>

                <!-- TEXTAREA AUTOEXPANDIBLE -->
                <div class="group">
                    <label class="label">Descripción Personalizada</label>
                    <div class="auto-textarea-container">
                        <textarea id="promptText" class="form-control auto-textarea" rows="2" placeholder="Ingresa una descripción personalizada o selecciona una sugerencia..." maxlength="2500"></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted char-count">0/2500</small>
                        <button class="btn btn-outline-secondary btn-sm" id="clearPrompt">
                            <i class="fas fa-eraser"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- OUTPUT SETTINGS -->
            <div class="section">
                <div class="title d-flex align-items-center">
                    <i class="fas fa-cogs mr-2"></i>
                    <span>Configuración de Salida</span>
                </div>

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

                <!-- OUTPUT COUNT -->
                <div class="group">
                    <label class="label">Cantidad de Imágenes</label>
                    <select class="form-control selector" id="outputCount" disabled>
                        <option value="1" selected>1 Resultado (Fijo)</option>
                    </select>
                </div>

                <!-- GENERATE BUTTON -->
                <button class="btn btn-success btn-lg btn-block btn-generate mt-4" id="generateVirtualModel">
                    <i class="fas fa-user-plus"></i> Generar Modelo Virtual
                </button>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right-panel">
        <div class="results-header">
            <i class="fas fa-users"></i>
            <span><b>Modelos Virtuales Generados</b></span>
        </div>
        <div class="results-content">
            <div id="virtualModelResults">
                <!-- Results will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- TOOLTIP CONFIGURACIÓN - TAMAÑO REDUCIDO -->
<div class="tooltip model-guidelines compact-tooltip" id="settingsTooltip">
    <div class="tooltip-header bg-light border-bottom-dark d-flex justify-content-between align-items-center p-2">
        <h6 class="mb-0 text-dark d-flex align-items-center">
            <i class="fas fa-cog mr-2"></i> Configuración
        </h6>
        <button class="btn btn-outline-danger btn-sm" onclick="hideTooltip('settingsTooltip')">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="tooltip-content p-2" style="max-height: 50vh; overflow-y: auto;">
        <div class="alert alert-info py-1 px-2 mb-3">
            <small><strong>Configura las características básicas</strong></small>
        </div>

        <div class="mb-3">
            <h6 class="text-primary mb-1"><i class="fas fa-mars-and-venus"></i> Género</h6>
            <small class="text-muted">Masculino o Femenino</small>
        </div>

        <div class="mb-3">
            <h6 class="text-success mb-1"><i class="fas fa-birthday-cake"></i> Edad</h6>
            <small class="text-muted">Niños (8-12) | Jóvenes (18-30) | Adultos (30+)</small>
        </div>

        <div class="mb-3">
            <h6 class="text-warning mb-1"><i class="fas fa-palette"></i> Tono de Piel</h6>
            <div class="d-flex justify-content-between">
                <div class="text-center">
                    <div class="color-btn-mini mb-1" style="background: rgb(255, 241, 228);"></div>
                    <small>Claro</small>
                </div>
                <div class="text-center">
                    <div class="color-btn-mini mb-1" style="background: rgb(250, 201, 145);"></div>
                    <small>Medio</small>
                </div>
                <div class="text-center">
                    <div class="color-btn-mini mb-1" style="background: rgb(143, 81, 40);"></div>
                    <small>Oscuro</small>
                </div>
                <div class="text-center">
                    <div class="color-btn-mini mb-1" style="background: rgb(195, 151, 112);"></div>
                    <small>Oliva</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TOOLTIP PROMPTS - TAMAÑO REDUCIDO -->
<div class="tooltip model-guidelines compact-tooltip" id="promptTooltip">
    <div class="tooltip-header bg-light border-bottom-dark d-flex justify-content-between align-items-center p-2">
        <h6 class="mb-0 text-dark d-flex align-items-center">
            <i class="fas fa-edit mr-2"></i> Prompts
        </h6>
        <button class="btn btn-outline-danger btn-sm" onclick="hideTooltip('promptTooltip')">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="tooltip-content p-2" style="max-height: 50vh; overflow-y: auto;">
        <div class="alert alert-info py-1 px-2 mb-3">
            <small><strong>Personaliza la apariencia del modelo</strong></small>
        </div>

        <div class="mb-3">
            <h6 class="text-primary mb-1"><i class="fas fa-lightbulb"></i> Sugerencias</h6>
            <small class="text-muted">Usa los botones predefinidos o combínalos</small>
        </div>

        <div class="mb-3">
            <h6 class="text-success mb-1"><i class="fas fa-pen-fancy"></i> Personalizado</h6>
            <small class="text-muted">Describe: expresiones, cabello, accesorios, ropa, poses</small>
        </div>

        <div class="alert alert-warning py-1 px-2">
            <small><strong>Tip:</strong> Combina sugerencias con descripciones personalizadas</small>
        </div>
    </div>
</div>

<!-- MODAL MEJORADO CON ZOOM -->
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
                <div class="image-viewer-container">
                    <img id="modalImage" src="" alt="Virtual Model">

                    <!-- INDICADOR DE ZOOM -->
                    <div class="zoom-indicator">
                        <i class="fas fa-search-plus"></i> <span id="zoomIndicator">100%</span>
                    </div>

                    <!-- AYUDA VISUAL -->
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
window.allHints = @json($hints);
</script>
@endsection

@push('scripts')
<script>
let modelHistory = [];
let selectedHint = null;
let isPromptManuallyEdited = false;

// VARIABLES PARA ZOOM MEJORADO
let currentZoom = 1;
let currentImageSrc = '';
let isDragging = false;
let startX, startY, translateX = 0, translateY = 0;

// VARIABLES PARA HINTS DINÁMICOS
let allHints = window.allHints || [];
let currentHints = [];
let usedHintIndices = [];

$(document).ready(function() {
    initializeExistingData();
    initializeBootstrapSelectors();
    initializeHints();
    initializePrompt();
    initializeGeneration();
    initializeTooltips();
    initializeAutoTextarea();
    loadRandomHints();
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

// TOOLTIPS MEJORADOS
function initializeTooltips() {
    $('#settingsInfoBtn').click(e => {
        e.stopPropagation();
        hideTooltip('promptTooltip');
        toggleTooltip('settingsTooltip', e.target);
    });

    $('#promptInfoBtn').click(e => {
        e.stopPropagation();
        hideTooltip('settingsTooltip');
        toggleTooltip('promptTooltip', e.target);
    });

    $(document).click(() => {
        hideTooltip('settingsTooltip');
        hideTooltip('promptTooltip');
    });

    $('.tooltip').click(e => {
        e.stopPropagation();
    });
}

// SELECTORES CON BOOTSTRAP
function initializeBootstrapSelectors() {
    // Gender selection
    $('input[name="gender"]').change(function() {
        $('#hiddenGender').val($(this).data('gender'));
    });

    // Age selection
    $('input[name="age"]').change(function() {
        $('#hiddenAge').val($(this).data('age'));
    });

    // Skin tone selection - MEJORADO
    $('.color-btn').click(function() {
        $('.color-btn').removeClass('active');
        $(this).addClass('active');
        const skin = $(this).data('skin');
        $('#hiddenSkin').val(skin);
        $(`input[name="skin_tone"][data-skin="${skin}"]`).prop('checked', true);
    });

    // Aspect ratio selection
    $('input[name="aspect_ratio"]').change(function() {
        $('#hiddenRatio').val($(this).data('ratio'));
    });

    $('#hiddenOutputCount').val('1');
}

// HINTS DINÁMICOS MEJORADOS
function initializeHints() {
    $('#refreshHints').click(function() {
        $(this).find('i').addClass('fa-spin');
        setTimeout(() => {
            loadRandomHints();
            $(this).find('i').removeClass('fa-spin');
        }, 500);
    });
}

function loadRandomHints() {
    if (allHints.length <= 4) {
        currentHints = [...allHints];
        usedHintIndices = [];
    } else {
        currentHints = [];
        const tempIndices = [...Array(allHints.length).keys()];

        // Remover índices ya usados
        const availableIndices = tempIndices.filter(i => !usedHintIndices.includes(i));

        // Si no quedan suficientes, reiniciar
        if (availableIndices.length < 4) {
            usedHintIndices = [];
        }

        // Seleccionar 4 aleatorios
        const finalIndices = availableIndices.length >= 4 ? availableIndices : tempIndices;
        const shuffled = finalIndices.sort(() => 0.5 - Math.random());
        const selected = shuffled.slice(0, 4);

        usedHintIndices.push(...selected);
        currentHints = selected.map(i => allHints[i]);
    }

    renderHints();
}

function renderHints() {
    const container = $('#hintsContainer');
    let html = '';

    currentHints.forEach(hint => {
        const iconClass = hint.key === 'elegante' ? 'gem' :
                         hint.key === 'urbano' ? 'city' :
                         hint.key === 'energetico' ? 'bolt' :
                         hint.key === 'dulce' ? 'heart' : 'glasses';

        html += `
            <button class="btn btn-outline-info btn-sm hint-btn" data-hint="${hint.key}" data-prompt="${hint.prompt}">
                <i class="fas fa-${iconClass}"></i>
                <small>${hint.name}</small>
            </button>
        `;
    });

    container.html(html);

    // Reinicializar eventos
    $('.hint-btn').click(function() {
        const hint = $(this).data('hint');
        const hintPrompt = $(this).data('prompt');

        if (selectedHint === hint) {
            $(this).removeClass('btn-info').addClass('btn-outline-info');
            selectedHint = null;
            if (!isPromptManuallyEdited) {
                $('#promptText').val('');
                updateCharCount();
                adjustTextareaHeight();
            }
        } else {
            $('.hint-btn').removeClass('btn-info').addClass('btn-outline-info');
            $(this).removeClass('btn-outline-info').addClass('btn-info');
            selectedHint = hint;
            if (!isPromptManuallyEdited) {
                $('#promptText').val(hintPrompt);
                updateCharCount();
                adjustTextareaHeight();
            }
        }
    });
}

// TEXTAREA AUTOEXPANDIBLE
function initializeAutoTextarea() {
    $('#promptText').on('input', function() {
        const value = $(this).val().trim();
        updateCharCount();
        adjustTextareaHeight();
        isPromptManuallyEdited = value.length > 0;
        $('#hiddenPrompt').val(value);
    });
}

function adjustTextareaHeight() {
    const textarea = $('#promptText')[0];
    textarea.style.height = 'auto';

    // Calcular altura necesaria
    const scrollHeight = textarea.scrollHeight;
    const minHeight = 48; // 2 rows aprox
    const maxHeight = 200; // máximo 8 rows aprox

    const newHeight = Math.min(Math.max(scrollHeight, minHeight), maxHeight);
    textarea.style.height = newHeight + 'px';

    // Si llegó al máximo, habilitar scroll
    if (scrollHeight > maxHeight) {
        textarea.style.overflowY = 'auto';
    } else {
        textarea.style.overflowY = 'hidden';
    }
}

function initializePrompt() {
    $('#clearPrompt').click(function() {
        $('#promptText').val('');
        $('#hiddenPrompt').val('');
        $('.hint-btn').removeClass('btn-info').addClass('btn-outline-info');
        selectedHint = null;
        isPromptManuallyEdited = false;
        updateCharCount();
        adjustTextareaHeight();
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
    formData.append('_token', $('input[name="_token"]').val());
    formData.append('gender', $('#hiddenGender').val());
    formData.append('age_group', $('#hiddenAge').val());
    formData.append('skin_tone', $('#hiddenSkin').val());
    formData.append('aspect_ratio', $('#hiddenRatio').val());
    formData.append('output_count', $('#hiddenOutputCount').val());
    formData.append('prompt', $('#hiddenPrompt').val());

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
        error: (xhr) => {
            tempResult.status = 'failed';
            updateModelInHistory(tempResult);
            resetGenerateButton();

            // ERROR CON SWEETALERT2
            let errorMessage = 'Error del servidor';
            if (xhr.responseJSON?.message) {
                errorMessage = xhr.responseJSON.message;
            }

            Swal.fire({
                icon: 'error',
                title: 'Error de generación',
                text: errorMessage
            });
        }
    });
}

// FUNCIONES DE ZOOM MEJORADAS (IGUALES AL TRY-ON)
const openImageModal = (imageSrc, title) => {
    currentImageSrc = imageSrc;
    currentZoom = 1;
    translateX = 0;
    translateY = 0;
    $('#modalImage').attr('src', imageSrc);
    $('.modal-title').html(`<i class="fas fa-user"></i> ${title}`);
    $('#zoomLevel').text('100%');
    $('#zoomIndicator').text('100%');
    updateImageTransform();
    $('#imageModal').modal('show');
};

const zoomImageAtCursor = (factor, mouseX, mouseY) => {
    const oldZoom = currentZoom;
    currentZoom *= factor;
    currentZoom = Math.max(0.5, Math.min(currentZoom, 5));

    const container = $('.image-viewer-container');
    const containerRect = container[0].getBoundingClientRect();
    const containerCenterX = containerRect.width / 2;
    const containerCenterY = containerRect.height / 2;

    const mouseRelativeX = mouseX - containerRect.left - containerCenterX;
    const mouseRelativeY = mouseY - containerRect.top - containerCenterY;

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
    link.download = `virtual-model-${Date.now()}.jpg`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

// EVENTOS DE DRAG Y ZOOM MEJORADOS
$('#modalImage').on('mousedown', function(e) {
    e.preventDefault();
    if (currentZoom <= 1) return;

    isDragging = true;
    startX = e.clientX - translateX;
    startY = e.clientY - translateY;

    $(this).css({
        'cursor': 'grabbing',
        'user-select': 'none'
    });

    $('.image-viewer-container').addClass('dragging');
    $('body').css('user-select', 'none');
});

$(document).on('mousemove', function(e) {
    if (!isDragging) return;

    e.preventDefault();

    const container = $('.image-viewer-container');
    const containerWidth = container.width();
    const containerHeight = container.height();

    const maxTranslateX = containerWidth * 0.5;
    const maxTranslateY = containerHeight * 0.5;

    const newTranslateX = e.clientX - startX;
    const newTranslateY = e.clientY - startY;

    translateX = Math.max(-maxTranslateX, Math.min(maxTranslateX, newTranslateX));
    translateY = Math.max(-maxTranslateY, Math.min(maxTranslateY, newTranslateY));

    updateImageTransform();
});

$(document).on('mouseup', function(e) {
    if (!isDragging) return;

    isDragging = false;

    $('#modalImage').css({
        'cursor': currentZoom > 1 ? 'grab' : 'default',
        'user-select': 'auto'
    });

    $('.image-viewer-container').removeClass('dragging');
    $('body').css('user-select', 'auto');
});

$('#modalImage').on('wheel', function(e) {
    e.preventDefault();

    const delta = e.originalEvent.deltaY;
    const factor = delta > 0 ? 0.9 : 1.1;

    const mouseX = e.clientX;
    const mouseY = e.clientY;

    zoomImageAtCursor(factor, mouseX, mouseY);
});

window.zoomImage = (factor) => {
    const container = $('.image-viewer-container');
    const containerRect = container[0].getBoundingClientRect();
    const centerX = containerRect.left + containerRect.width / 2;
    const centerY = containerRect.top + containerRect.height / 2;

    zoomImageAtCursor(factor, centerX, centerY);
};

$('#modalImage').on('dblclick', function(e) {
    e.preventDefault();

    if (currentZoom === 1) {
        const mouseX = e.clientX;
        const mouseY = e.clientY;
        zoomImageAtCursor(2, mouseX, mouseY);
    } else {
        resetZoom();
    }
});

// FUNCIONES TOOLTIP
const toggleTooltip = (tooltipId, targetElement) => {
    const tooltip = document.getElementById(tooltipId);
    const rect = targetElement.getBoundingClientRect();
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;

    if (tooltip.style.display === 'block') {
        hideTooltip(tooltipId);
    } else {
        tooltip.style.display = 'block';

        // Tamaño reducido para tooltips compactos
        let left = rect.left - 300;
        if (left < 10) left = 10;
        if (left + 600 > viewportWidth) left = viewportWidth - 610;

        let top = rect.bottom + 10;
        if (top + 300 > viewportHeight) top = rect.top - 310;
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

        // Traducir valores
        let genderText = model.gender === 'male' ? 'Masculino' : 'Femenino';
        let ageText = model.age_group === 'children' ? 'Niños' : (model.age_group === 'youth' ? 'Jóvenes' : 'Adultos');
        let skinText = model.skin_tone === 'light' ? 'Claro' : (model.skin_tone === 'medium' ? 'Medio' : (model.skin_tone === 'dark' ? 'Oscuro' : 'Oliva'));
        let statusText = model.status === 'processing' ? 'En Proceso' : (model.status === 'completed' ? 'Completado' : 'Fallido');

        html += `
            <div class="result-group">
                <div class="result-header">
                    <div class="result-info">
                        <div><h6>${model.display_name || 'Modelo Virtual'}</h6></div>
                        <div><b>Fecha y hora de creación: </b>${date}</div>
                        <div><b>Género: </b>${genderText} | <b>Edad: </b>${ageText} | <b>Piel: </b>${skinText}</div>
                        ${model.prompt ? `<div class="prompt-used"><b>Prompt:</b> ${model.prompt.substring(0, 100)}${model.prompt.length > 100 ? '...' : ''}</div>` : ''}
                    </div>
                    <div class="${statusClass}">${statusText.toUpperCase()}</div>
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
                <small>⏳ Tiempo estimado: 10–30 segundos.</small>
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

const showEmptyState = () => {
    $('#virtualModelResults').html(`
        <div class="text-center">
            <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Aún no hay modelos virtuales</h5>
            <p class="text-muted">Configura los ajustes y genera tu primer modelo virtual</p>
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
