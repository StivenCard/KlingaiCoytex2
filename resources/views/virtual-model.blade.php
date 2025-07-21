@extends('layouts.probador-virtual-ia')

@section('content')
<div class="main-container">
    <!-- LEFT PANEL -->
    <div class="col-12 col-lg-4 d-flex flex-column overflow-hidden px-2 h-100" style="align-items:stretch">
        <div class="card">
            <div class="card-header">
                <!-- TÍTULO -->
                <b>Configuración del Modelo Virtual</b>
            </div>
            <div class="card-body">
                <!-- SCROLL CONTAINER -->
                <div class="">
                    <!-- MODEL SETTINGS -->
                    <div class="section mt-0">
                        <div class="title d-flex align-items-center">
                            <i class="fas fa-cog mr-2"></i>
                            <span>Configuración del Modelo</span>
                            {{-- <button class="btn btn-outline-info btn-sm info-btn ml-auto" id="settingsInfoBtn" title="Guía de Configuración">
                                <i class="fas fa-info-circle"></i>
                            </button> --}}
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
                            {{-- <button class="btn btn-outline-info btn-sm info-btn ml-auto" id="promptInfoBtn" title="Guía de Prompts">
                                <i class="fas fa-info-circle"></i>
                            </button> --}}
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
                        <button class="btn btn-success btn-block btn-generate mt-4" id="generateVirtualModel">
                            <i class="fas fa-user-plus"></i> Generar Modelo Virtual
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="col-12 col-lg-8 d-flex flex-column overflow-hidden px-2 h-100" style="align-items:stretch">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-users"></i>
                <span><b>Modelos Virtuales Generados</b></span>
            </div>
            <div class="card-body">
                <div id="virtualModelResults">
                    <!-- Results will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DE IMAGEN CON ZOOM -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-eye"></i> Vista Previa</h5>
        <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="modal-body p-0">
        <div class="image-viewer-container">
          <img id="modalImage" src="" alt="Vista previa">
          <div class="zoom-indicator"><i class="fas fa-search-plus"></i> <span id="zoomIndicator">100%</span></div>
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
                <button class="btn btn-primary btn-sm" onclick="zoomImage(0.8)">
                  <i class="fas fa-search-minus"></i>
                </button>
                <span id="zoomLevel" class="mx-2 badge badge-secondary">100%</span>
                <button class="btn btn-primary btn-sm" onclick="zoomImage(1.25)">
                  <i class="fas fa-search-plus"></i>
                </button>
                <button class="btn btn-outline-primary btn-sm ml-2" onclick="resetZoom()">
                  <i class="fas fa-expand-arrows-alt"></i> Ajustar
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
                title: 'Error en la generación',
                text: errorMessage
            });
        }
    });
}

// FUNCIONES DE ZOOM MEJORADAS (IGUALES AL TRY-ON)
function openImageModal(imageSrc, title = 'Vista Previa') {
    currentImageSrc = imageSrc;
    currentZoom = 1;
    translateX = 0;
    translateY = 0;

    $('#modalImage').attr('src', imageSrc);
    $('.modal-title').html(`<i class="fas fa-eye"></i> ${title}`);
    $('#zoomLevel, #zoomIndicator').text('100%');
    updateImageTransform();
    $('#imageModal').modal('show');
}

function zoomImageAtCursor(factor, mouseX, mouseY) {
    const oldZoom = currentZoom;
    currentZoom *= factor;
    currentZoom = Math.max(0.5, Math.min(currentZoom, 5));

    const container = $('.image-viewer-container')[0].getBoundingClientRect();
    const mouseRelX = mouseX - container.left - container.width / 2;
    const mouseRelY = mouseY - container.top - container.height / 2;

    const zoomRatio = currentZoom / oldZoom;
    translateX = mouseRelX - (mouseRelX - translateX) * zoomRatio;
    translateY = mouseRelY - (mouseRelY - translateY) * zoomRatio;

    updateImageTransform();
    $('#zoomLevel, #zoomIndicator').text(Math.round(currentZoom * 100) + '%');
}

function resetZoom() {
    currentZoom = 1;
    translateX = 0;
    translateY = 0;
    updateImageTransform();
    $('#zoomLevel, #zoomIndicator').text('100%');
}

function updateImageTransform() {
    $('#modalImage').css({
        transform: `translate(${translateX}px, ${translateY}px) scale(${currentZoom})`,
        transition: isDragging ? 'none' : 'transform 0.1s ease-out'
    });
}

function downloadImage() {
    if (!currentImageSrc) return;
    const link = document.createElement('a');
    link.href = currentImageSrc;
    link.download = `imagen-${Date.now()}.jpg`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// EVENTOS DE DRAG Y ZOOM MEJORADOS
$('#modalImage').on('mousedown', function(e) {
    if (currentZoom <= 1) return;
    isDragging = true;
    startX = e.clientX - translateX;
    startY = e.clientY - translateY;
    $(this).css('cursor', 'grabbing');
    $('body').css('user-select', 'none');
});

$(document).on('mousemove', function(e) {
    if (!isDragging) return;
    translateX = e.clientX - startX;
    translateY = e.clientY - startY;
    updateImageTransform();
});

$(document).on('mouseup', function() {
    isDragging = false;
    $('#modalImage').css('cursor', currentZoom > 1 ? 'grab' : 'default');
    $('body').css('user-select', 'auto');
});

$('#modalImage').on('wheel', function(e) {
    e.preventDefault();
    const factor = e.originalEvent.deltaY > 0 ? 0.9 : 1.1;
    zoomImageAtCursor(factor, e.clientX, e.clientY);
});

function zoomImage(factor) {
    const container = $('.image-viewer-container')[0].getBoundingClientRect();
    const centerX = container.left + container.width / 2;
    const centerY = container.top + container.height / 2;
    zoomImageAtCursor(factor, centerX, centerY);
}

$('#modalImage').on('dblclick', function(e) {
    if (currentZoom === 1) {
        zoomImageAtCursor(2, e.clientX, e.clientY);
    } else {
        resetZoom();
    }
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
