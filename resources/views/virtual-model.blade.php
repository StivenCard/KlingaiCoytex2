@extends('layouts.app')

@section('content')
<div class="main-container">
    <!-- LEFT PANEL -->
    <div class="left-panel">
        <!-- TAB HEADER -->
        <div class="sub-tabs">
            <div class="sub-tab active" style="flex: 1; text-align: center; padding: 12px; background: var(--bg-3); color: var(--txt-1);">
                <i class="fas fa-user-plus"></i> Generar Modelo Virtual
            </div>
        </div>

        <!-- SCROLL CONTAINER -->
        <div class="scroll-container">
            <!-- MODEL SETTINGS -->
            <div class="section">
                <h6 class="title"><i class="fas fa-cog"></i> Configuración del Modelo</h6>

                <!-- GENDER -->
                <div class="group">
                    <label class="label">Género</label>
                    <div class="h-selector">
                        <button class="selector-btn active" data-gender="male">
                            <i class="fas fa-mars"></i> Masculino
                        </button>
                        <button class="selector-btn" data-gender="female">
                            <i class="fas fa-venus"></i> Femenino
                        </button>
                    </div>
                </div>

                <!-- AGE -->
                <div class="group">
                    <label class="label">Edad</label>
                    <div class="h-selector">
                        <button class="selector-btn" data-age="children">
                            <i class="fas fa-child"></i> Niños
                        </button>
                        <button class="selector-btn active" data-age="youth">
                            <i class="fas fa-user"></i> Jóvenes
                        </button>
                        <button class="selector-btn" data-age="elderly">
                            <i class="fas fa-user-tie"></i> Adultos
                        </button>
                    </div>
                </div>

                <!-- SKIN TONE -->
                <div class="group">
                    <label class="label">Tono de Piel</label>
                    <div class="h-selector">
                        <button class="selector-btn color-btn active" data-skin="light" style="background: rgb(255, 241, 228);">
                            <span class="color-check"><i class="fas fa-check"></i></span>
                        </button>
                        <button class="selector-btn color-btn" data-skin="medium" style="background: rgb(250, 201, 145);">
                            <span class="color-check"><i class="fas fa-check"></i></span>
                        </button>
                        <button class="selector-btn color-btn" data-skin="dark" style="background: rgb(143, 81, 40);">
                            <span class="color-check"><i class="fas fa-check"></i></span>
                        </button>
                        <button class="selector-btn color-btn" data-skin="olive" style="background: rgb(195, 151, 112);">
                            <span class="color-check"><i class="fas fa-check"></i></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- PROMPT SECTION -->
            <div class="section">
                <h6 class="title"><i class="fas fa-edit"></i> Prompt Personalizado (Opcional)</h6>

                <!-- HINTS -->
                <div class="group">
                    <label class="label">Sugerencias Rápidas</label>
                    <div class="grid-hints">
                        @foreach($hints as $hint)
                            <button class="hint-btn" data-hint="{{ $hint['key'] }}" data-prompt="{{ $hint['prompt'] }}">
                                <i class="fas fa-{{ $hint['key'] === 'elegante' ? 'gem' : ($hint['key'] === 'urbano' ? 'city' : ($hint['key'] === 'energetico' ? 'bolt' : ($hint['key'] === 'dulce' ? 'heart' : 'glasses'))) }}"></i>
                                {{ $hint['name'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- TEXTAREA -->
                <div class="group">
                    <div class="prompt-container">
                        <textarea id="promptText" class="prompt-textarea" placeholder="Ingresa un prompt personalizado o selecciona una sugerencia arriba..." maxlength="2500"></textarea>
                        <div class="prompt-footer">
                            <span class="char-count">0/2500</span>
                            <button class="clear-btn" id="clearPrompt">
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
                    <div class="h-selector">
                        <button class="selector-btn active" data-ratio="3:4">3:4</button>
                        <button class="selector-btn" data-ratio="2:3">2:3</button>
                        <button class="selector-btn" data-ratio="9:16">9:16</button>
                        <button class="selector-btn" data-ratio="1:1">1:1</button>
                    </div>
                </div>

                <!-- 🔥 OUTPUT COUNT BLOQUEADO -->
                <div class="group">
                    <label class="label">Cantidad de Imágenes</label>
                    <select class="selector" id="outputCount" disabled>
                        <option value="1" selected>1 Resultado (Fijo)</option>
                    </select>
                </div>

                <!-- GENERATE -->
                <button class="btn-generate" id="generateVirtualModel">
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

<!-- 🔥 MODAL PARA VER VIRTUAL MODELS -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="background: var(--bg-2); border: 1px solid var(--border);">
            <div class="modal-header" style="border-bottom: 1px solid var(--border);">
                <h5 class="modal-title" style="color: var(--txt-1);">
                    <i class="fas fa-user"></i> Vista Previa del Modelo Virtual
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1);"></button>
            </div>
            <div class="modal-body p-0">
                <!-- IMAGE CONTAINER -->
                <div class="image-viewer-container" style="position: relative; height: 70vh; overflow: hidden; background: var(--bg-1);">
                    <img id="modalImage" src="" alt="Virtual Model" style="width: 100%; height: 100%; object-fit: contain; cursor: grab; transition: transform 0.3s;">
                </div>

                <!-- CONTROLS -->
                <div class="image-controls" style="background: var(--bg-3); padding: 15px; border-top: 1px solid var(--border);">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <!-- ZOOM CONTROLS -->
                            <div class="zoom-controls">
                                <button class="btn btn-sm" style="background: var(--bg-2); border: 1px solid var(--border); color: var(--txt-1); margin-right: 5px;" onclick="zoomImage(0.9)">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                                <span style="color: var(--txt-2); margin: 0 10px; font-size: 14px;" id="zoomLevel">100%</span>
                                <button class="btn btn-sm" style="background: var(--bg-2); border: 1px solid var(--border); color: var(--txt-1); margin-left: 5px;" onclick="zoomImage(1.1)">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                                <button class="btn btn-sm" style="background: var(--bg-2); border: 1px solid var(--border); color: var(--txt-1); margin-left: 10px;" onclick="resetZoom()">
                                    <i class="fas fa-expand-arrows-alt"></i> Restablecer
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <!-- DOWNLOAD BUTTON -->
                            <button class="btn" style="background: var(--accent-1); border: none; color: white; padding: 8px 16px;" onclick="downloadImage()">
                                <i class="fas fa-download"></i> Descargar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- HIDDEN FORM - 🔧 SIMPLIFICADO: Solo campos esenciales -->
<form id="virtualModelForm" style="display: none;">
    @csrf
    <input type="hidden" name="gender" id="hiddenGender" value="male">
    <input type="hidden" name="age_group" id="hiddenAge" value="youth">
    <input type="hidden" name="skin_tone" id="hiddenSkin" value="light">
    <input type="hidden" name="aspect_ratio" id="hiddenRatio" value="3:4">
    <input type="hidden" name="output_count" id="hiddenOutputCount" value="1">
    <input type="hidden" name="prompt" id="hiddenPrompt">
</form>

<script>
// 🔧 SIMPLIFICADO: Obtener datos desde backend
window.existingModels = @json($existingModels);
</script>
@endsection

@push('scripts')
<script>
let modelHistory = [];
let selectedHint = null;
let isPromptManuallyEdited = false;

// 🔥 VARIABLES PARA ZOOM - MANTENIDAS COMPLETAS
let currentZoom = 1;
let currentImageSrc = '';
let isDragging = false;
let startX, startY, translateX = 0, translateY = 0;

$(document).ready(function() {
    // 🔧 SIMPLIFICADO: Inicialización de datos
    initializeExistingData();

    // ✅ MANTENIDO: Todas las funcionalidades UI
    initializeSelectors();
    initializeHints();
    initializePrompt();
    initializeGeneration();

    updateCharCount();
});

// 🔧 SIMPLIFICADO: Función de inicialización
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

// ✅ MANTENIDO COMPLETO: Selectores
function initializeSelectors() {
    const selectors = {
        '[data-gender]': '#hiddenGender',
        '[data-age]': '#hiddenAge',
        '[data-skin]': '#hiddenSkin',
        '[data-ratio]': '#hiddenRatio'
    };

    Object.entries(selectors).forEach(([selector, hidden]) => {
        $(selector).click(function() {
            $(selector).removeClass('active');
            $(this).addClass('active');
            $(hidden).val($(this).data(selector.slice(6, -1)));
        });
    });

    $('#hiddenOutputCount').val('1');
}

// ✅ MANTENIDO COMPLETO: Hints
function initializeHints() {
    $('.hint-btn').click(function() {
        const hint = $(this).data('hint');
        const hintPrompt = $(this).data('prompt');

        if (selectedHint === hint) {
            $(this).removeClass('active');
            selectedHint = null;
            if (!isPromptManuallyEdited) {
                $('#promptText').val('');
                updateCharCount();
            }
        } else {
            $('.hint-btn').removeClass('active');
            $(this).addClass('active');
            selectedHint = hint;
            if (!isPromptManuallyEdited) {
                $('#promptText').val(hintPrompt);
                updateCharCount();
            }
        }
    });
}

// ✅ MANTENIDO COMPLETO: Prompt
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
        $('.hint-btn').removeClass('active');
        selectedHint = null;
        isPromptManuallyEdited = false;
        updateCharCount();
    });
}

// ✅ MANTENIDO COMPLETO: Generación
function initializeGeneration() {
    $('#generateVirtualModel').click(function() {
        const prompt = $('#promptText').val().trim();
        if (prompt.length > 0) $('#hiddenPrompt').val(prompt);

        // 🔧 SIMPLIFICADO: Preparación de datos
        const formData = prepareFormData();

        // ✅ MANTENIDO: UI y AJAX
        submitGeneration(formData);
    });
}

// 🔧 SIMPLIFICADO: Preparación de FormData
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

// ✅ MANTENIDO COMPLETO: Envío y manejo
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

// ✅ MANTENIDAS COMPLETAS: Todas las funciones de zoom/modal/UI
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
    $('#modalImage').css('transform', `translate(${translateX}px, ${translateY}px) scale(${currentZoom})`);
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

// ✅ MANTENIDAS COMPLETAS: Funciones drag
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

// ✅ MANTENIDAS COMPLETAS: Utilidades
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
                html += `<div class="item" style="cursor: pointer; position: relative;" onclick="openImageModal('${imageSrc}', 'Modelo Virtual ${imgIndex + 1}')">
                    <img src="${imageSrc}" alt="Modelo ${imgIndex + 1}">
                    <div class="image-overlay" style="position: absolute; top: 8px; right: 8px; background: rgba(0,0,0,0.7); border-radius: 4px; padding: 4px 8px; opacity: 0; transition: opacity 0.3s;">
                        <i class="fas fa-search-plus" style="color: white; font-size: 12px;"></i>
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

    // ✅ MANTENIDO: Hover effect para overlay
    $('.item').hover(
        function() { $(this).find('.image-overlay').css('opacity', '1'); },
        function() { $(this).find('.image-overlay').css('opacity', '0'); }
    );
};

const showEmptyState = () => {
    $('#virtualModelResults').html(`
        <div class="text-center" style="padding: 60px 20px; color: var(--txt-2);">
            <i class="fas fa-user-plus" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
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
