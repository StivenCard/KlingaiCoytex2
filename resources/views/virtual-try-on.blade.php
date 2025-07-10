@extends('layouts.app')

@section('content')
<!-- HEADER -->
<header class="main-header">
    <div class="main-tabs">
        <a href="#" class="main-tab">Virtual Model</a>
        <a href="#" class="main-tab active">AI Virtual Try-On</a>

        <div class="help-icons">
            <a href="#" class="help-icon">
                <i class="far fa-question-circle"></i>
                User Guide
            </a>
            <a href="#" class="help-icon">
                <i class="far fa-lightbulb"></i>
                Guideline
            </a>
        </div>
    </div>
</header>

<!-- MAIN CONTAINER -->
<div class="main-container">
    <!-- LEFT PANEL -->
    <div class="left-panel">
        <!-- MODEL SELECTION TABS -->
        <div class="sub-tabs">
            <button class="sub-tab" data-tab="virtual">Virtual Model</button>
            <button class="sub-tab active" data-tab="default">Default</button>
            <button class="sub-tab" data-tab="upload">Upload</button>
        </div>

        <!-- MODEL GRID CONTAINER -->
        <div class="model-grid-container">
            <!-- VIRTUAL MODELS SECTION -->
            <div class="tab-content d-none" data-content="virtual">
                <div class="model-grid" id="virtualModelGrid">
                    <!-- Virtual models will be loaded here -->
                </div>
            </div>

            <!-- DEFAULT MODELS SECTION -->
            <div class="tab-content" data-content="default">
                <div class="model-grid" id="defaultModelGrid">
                    @forelse($defaultModels as $model)
                        <div class="model-item" data-model="{{ $model['filename'] }}">
                            <img src="{{ $model['url'] }}" alt="{{ $model['name'] }}">
                            <input type="radio" name="selected_default_model" value="{{ $model['filename'] }}" class="d-none">
                        </div>
                    @empty
                        <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                            <div style="color: var(--text-secondary);">
                                <i class="fas fa-images" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                                <p>No hay modelos default disponibles</p>
                                <small>Coloca imágenes en public/klingai/default_models/</small>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- UPLOAD MODELS SECTION -->
            <div class="tab-content d-none" data-content="upload">
                <div class="upload-area" id="humanUploadArea" onclick="triggerFileInput('humanImageInput')">
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <div class="upload-text">Upload Human Model</div>
                    <input type="file" id="humanImageInput" name="human_image" accept=".jpg,.jpeg,.png" class="file-input">

                    <!-- PREVIEW -->
                    <div class="image-preview" id="humanPreview">
                        <img src="" alt="Preview">
                        <button class="remove-btn" onclick="clearFileInput('humanImageInput', 'humanUploadArea', 'humanPreview')">
                            <i class="fas fa-times"></i>
                        </button>
                        <button class="reupload-btn" onclick="triggerFileInput('humanImageInput')">
                            <i class="fas fa-upload"></i>
                            Re-upload
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- GARMENT SECTION -->
        <div class="garment-section">
            <!-- GARMENT TABS -->
            <div class="garment-tabs">
                <button class="garment-tab active" data-garment="single">Single Garment</button>
                <button class="garment-tab" data-garment="multiple">Multiple Garments</button>
            </div>

            <!-- SINGLE GARMENT -->
            <div class="garment-content" data-garment-content="single">
                <div class="upload-area" id="singleUploadArea" onclick="triggerFileInput('singleGarmentInput')">
                    <div class="upload-icon">
                        <i class="fas fa-tshirt"></i>
                    </div>
                    <div class="upload-text">Upload Single Garment</div>
                    <input type="file" id="singleGarmentInput" name="single_garment" accept=".jpg,.jpeg,.png" class="file-input">

                    <!-- PREVIEW -->
                    <div class="image-preview" id="singlePreview">
                        <img src="" alt="Preview">
                        <button class="remove-btn" onclick="clearFileInput('singleGarmentInput', 'singleUploadArea', 'singlePreview')">
                            <i class="fas fa-times"></i>
                        </button>
                        <button class="reupload-btn" onclick="triggerFileInput('singleGarmentInput')">
                            <i class="fas fa-upload"></i>
                            Re-upload
                        </button>
                    </div>
                </div>
            </div>

            <!-- MULTIPLE GARMENTS -->
            <div class="garment-content d-none" data-garment-content="multiple">
                <div class="upload-area" id="topUploadArea" onclick="triggerFileInput('topGarmentInput')" style="margin-bottom: 12px;">
                    <div class="upload-icon">
                        <i class="fas fa-tshirt"></i>
                    </div>
                    <div class="upload-text">Upload Top Garment</div>
                    <input type="file" id="topGarmentInput" name="top_garment" accept=".jpg,.jpeg,.png" class="file-input">

                    <!-- PREVIEW -->
                    <div class="image-preview" id="topPreview">
                        <img src="" alt="Preview">
                        <button class="remove-btn" onclick="clearFileInput('topGarmentInput', 'topUploadArea', 'topPreview')">
                            <i class="fas fa-times"></i>
                        </button>
                        <button class="reupload-btn" onclick="triggerFileInput('topGarmentInput')">
                            <i class="fas fa-upload"></i>
                            Re-upload
                        </button>
                    </div>
                </div>

                <div class="upload-area" id="bottomUploadArea" onclick="triggerFileInput('bottomGarmentInput')">
                    <div class="upload-icon">
                        <i class="fas fa-tshirt"></i>
                    </div>
                    <div class="upload-text">Upload Bottom Garment</div>
                    <input type="file" id="bottomGarmentInput" name="bottom_garment" accept=".jpg,.jpeg,.png" class="file-input">

                    <!-- PREVIEW -->
                    <div class="image-preview" id="bottomPreview">
                        <img src="" alt="Preview">
                        <button class="remove-btn" onclick="clearFileInput('bottomGarmentInput', 'bottomUploadArea', 'bottomPreview')">
                            <i class="fas fa-times"></i>
                        </button>
                        <button class="reupload-btn" onclick="triggerFileInput('bottomGarmentInput')">
                            <i class="fas fa-upload"></i>
                            Re-upload
                        </button>
                    </div>
                </div>
            </div>

            <!-- OUTPUT SELECTOR -->
            <select class="output-selector" name="output_count">
                <option value="1">1 Output</option>
                <option value="2">2 Outputs</option>
                <option value="3">3 Outputs</option>
                <option value="4">4 Outputs</option>
            </select>

            <!-- GENERATE BUTTON -->
            <button class="generate-btn" id="generateBtn">
                <i class="fas fa-magic"></i>
                Generate
            </button>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right-panel">
        <div class="results-header">
            <i class="fas fa-images"></i>
            <span>AI Outfit</span>
        </div>

        <div class="results-content">
            <div class="result-section">
                <div id="resultsContainer">
                    <!-- Results will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- HIDDEN FORM FOR SUBMISSION -->
<form id="hiddenForm" style="display: none;" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="model_source" id="hiddenModelSource">
    <input type="hidden" name="selected_default_model" id="hiddenSelectedModel">
    <input type="hidden" name="garment_type" id="hiddenGarmentType" value="single">
    <input type="hidden" name="output_count" id="hiddenOutputCount" value="1">
</form>

<!-- 🔥 NUEVO: PASAR DATOS DEL SERVIDOR A JAVASCRIPT -->
<script>
    window.existingTryOns = @json($existingTryOns);
</script>
@endsection

@push('scripts')
<script>
// GLOBAL VARIABLES
let resultsHistory = [];

$(document).ready(function() {
    // 🔥 CAMBIO 4: CARGAR RESULTADOS EXISTENTES DEL SERVIDOR
    if (window.existingTryOns && window.existingTryOns.length > 0) {
        resultsHistory = window.existingTryOns;
        renderResultsHistory();
    } else {
        showEmptyState();
    }

    // TAB SWITCHING
    $('.sub-tab').click(function() {
        const tab = $(this).data('tab');

        $('.sub-tab').removeClass('active');
        $(this).addClass('active');

        $('.tab-content').addClass('d-none');
        $(`[data-content="${tab}"]`).removeClass('d-none');

        $('#hiddenModelSource').val(tab);

        $('.model-item').removeClass('selected');
        $('input[name="selected_default_model"]').prop('checked', false);
        $('#hiddenSelectedModel').val('');
    });

    // GARMENT TAB SWITCHING
    $('.garment-tab').click(function() {
        const garment = $(this).data('garment');

        $('.garment-tab').removeClass('active');
        $(this).addClass('active');

        $('.garment-content').addClass('d-none');
        $(`[data-garment-content="${garment}"]`).removeClass('d-none');

        $('#hiddenGarmentType').val(garment);
    });

    // MODEL SELECTION
    $(document).on('click', '.model-item', function() {
        $('.model-item').removeClass('selected');
        $(this).addClass('selected');

        const modelValue = $(this).data('model');
        $(this).find('input[name="selected_default_model"]').prop('checked', true);
        $('#hiddenSelectedModel').val(modelValue);
    });

    // OUTPUT COUNT UPDATE
    $('.output-selector').change(function() {
        $('#hiddenOutputCount').val($(this).val());
    });

    // FILE INPUT HANDLERS CON PREVIEW
    $('#humanImageInput').change(function() {
        if (this.files.length > 0) {
            showImagePreview(this.files[0], 'humanPreview', 'humanUploadArea');
        }
    });

    $('#singleGarmentInput').change(function() {
        if (this.files.length > 0) {
            showImagePreview(this.files[0], 'singlePreview', 'singleUploadArea');
        }
    });

    $('#topGarmentInput').change(function() {
        if (this.files.length > 0) {
            showImagePreview(this.files[0], 'topPreview', 'topUploadArea');
        }
    });

    $('#bottomGarmentInput').change(function() {
        if (this.files.length > 0) {
            showImagePreview(this.files[0], 'bottomPreview', 'bottomUploadArea');
        }
    });

    // GENERATE BUTTON
    $('#generateBtn').click(function() {
        const modelSource = $('#hiddenModelSource').val() || 'default';
        const garmentType = $('#hiddenGarmentType').val();

        // Validation
        if (modelSource === 'default' && !$('#hiddenSelectedModel').val()) {
            alert('Please select a default model');
            return;
        }

        if (modelSource === 'upload' && !$('#humanImageInput')[0].files.length) {
            alert('Please upload a human model image');
            return;
        }

        if (garmentType === 'single' && !$('#singleGarmentInput')[0].files.length) {
            alert('Please upload a garment');
            return;
        }

        if (garmentType === 'multiple') {
            if (!$('#topGarmentInput')[0].files.length || !$('#bottomGarmentInput')[0].files.length) {
                alert('Please upload both top and bottom garments');
                return;
            }
        }

        // Prepare FormData
        const formData = new FormData();
        formData.append('_token', $('input[name="_token"]').val());
        formData.append('model_source', modelSource);
        formData.append('garment_type', garmentType);
        formData.append('output_count', $('#hiddenOutputCount').val());

        if (modelSource === 'default') {
            formData.append('selected_default_model', $('#hiddenSelectedModel').val());
        } else if (modelSource === 'upload') {
            formData.append('human_image', $('#humanImageInput')[0].files[0]);
        }

        if (garmentType === 'single') {
            formData.append('single_garment', $('#singleGarmentInput')[0].files[0]);
        } else {
            formData.append('top_garment', $('#topGarmentInput')[0].files[0]);
            formData.append('bottom_garment', $('#bottomGarmentInput')[0].files[0]);
        }

        // UI feedback
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generating...');

        // Create temporary result entry
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

        // AJAX call
        $.ajax({
            url: "{{ route('virtual-try-on.generate') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Success:', response);
                if (response.data && response.data.task_id) {
                    tempResult.task_id = response.data.task_id;
                    updateResultInHistory(tempResult);
                    checkTaskStatus(response.data.task_id);
                } else {
                    tempResult.status = 'failed';
                    updateResultInHistory(tempResult);
                    resetGenerateButton();
                }
            },
            error: function(xhr) {
                console.log('Error:', xhr.responseJSON);
                tempResult.status = 'failed';
                updateResultInHistory(tempResult);
                resetGenerateButton();
            }
        });
    });

    // Initialize
    $('#hiddenModelSource').val('default');
    loadVirtualModels();
});

// 🔥 CORREGIDO: FUNCIÓN PARA TRIGGER FILE INPUT
function triggerFileInput(inputId) {
    event.stopPropagation();
    document.getElementById(inputId).click();
}

// MOSTRAR PREVIEW DE IMAGEN
function showImagePreview(file, previewId, uploadAreaId) {
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById(previewId);
        const uploadArea = document.getElementById(uploadAreaId);

        preview.querySelector('img').src = e.target.result;
        preview.style.display = 'block';
        uploadArea.classList.add('has-file');

        // Hide upload content
        uploadArea.querySelector('.upload-icon').style.display = 'none';
        uploadArea.querySelector('.upload-text').style.display = 'none';
    };
    reader.readAsDataURL(file);
}

// LIMPIAR INPUT DE ARCHIVO
function clearFileInput(inputId, uploadAreaId, previewId) {
    event.stopPropagation();

    const input = document.getElementById(inputId);
    const uploadArea = document.getElementById(uploadAreaId);
    const preview = document.getElementById(previewId);

    input.value = '';
    preview.style.display = 'none';
    uploadArea.classList.remove('has-file');

    // Show upload content
    uploadArea.querySelector('.upload-icon').style.display = 'block';
    uploadArea.querySelector('.upload-text').style.display = 'block';
}

// MOSTRAR ESTADO VACÍO
function showEmptyState() {
    $('#resultsContainer').html(`
        <div id="emptyState" class="text-center" style="padding: 60px 20px; color: var(--text-secondary);">
            <i class="fas fa-tshirt" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
            <p>No results yet</p>
            <small>Complete the form to generate your first try-on</small>
        </div>
    `);
}

// AGREGAR RESULTADO AL HISTORIAL
function addResultToHistory(result) {
    resultsHistory.unshift(result);
    renderResultsHistory();
}

// ACTUALIZAR RESULTADO EN HISTORIAL
function updateResultInHistory(updatedResult) {
    const index = resultsHistory.findIndex(r => r.id === updatedResult.id || r.task_id === updatedResult.task_id);
    if (index !== -1) {
        resultsHistory[index] = { ...resultsHistory[index], ...updatedResult };
        renderResultsHistory();
    }
}

// RENDERIZAR HISTORIAL
function renderResultsHistory() {
    const container = $('#resultsContainer');

    if (resultsHistory.length === 0) {
        showEmptyState();
        return;
    }

    let html = '';

    resultsHistory.forEach((result, index) => {
        const statusClass = result.status === 'completed' ? 'status-completed' :
                           result.status === 'processing' ? 'status-processing' : 'status-failed';

        const date = new Date(result.created_at).toLocaleString();

        html += `
            <div class="result-group">
                <div class="result-group-header">
                    <div class="result-group-info">
                        <div><strong>Task ${result.task_id || 'Pending'}</strong></div>
                        <div>${date}</div>
                        <div>Model: ${result.model_type} | Garments: ${result.garments_type}</div>
                    </div>
                    <div class="result-group-status ${statusClass}">
                        ${result.status.toUpperCase()}
                    </div>
                </div>
                <div class="result-grid" id="result-grid-${index}">
        `;

        if (result.result_image_paths && result.result_image_paths.length > 0) {
            result.result_image_paths.forEach((path, imgIndex) => {
                html += `
                    <div class="result-item">
                        <img src="${path}" alt="Result ${imgIndex + 1}">
                    </div>
                `;
            });
        } else if (result.status === 'processing') {
            html += `
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-secondary);">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p style="margin-top: 16px;">Processing...</p>
                </div>
            `;
        } else {
            html += `
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-secondary);">
                    <i class="fas fa-exclamation-circle" style="font-size: 32px; margin-bottom: 16px; opacity: 0.5;"></i>
                    <p>No results available</p>
                </div>
            `;
        }

        html += `
                </div>
            </div>
        `;
    });

    container.html(html);
}

// POLLING FUNCTION (UPDATED)
function checkTaskStatus(taskId) {
    setTimeout(function poll() {
        $.get(`/virtual-try-on/status/${taskId}`, function(response) {
            if (response.data) {
                const status = response.data.task_status;
                if (status === 'succeed') {
                    const result = resultsHistory.find(r => r.task_id === taskId);
                    if (result) {
                        result.status = 'completed';
                        result.result_image_paths = response.data.task_result.images.map(img => img.url);
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
        }).fail(function() {
            const result = resultsHistory.find(r => r.task_id === taskId);
            if (result) {
                result.status = 'failed';
                updateResultInHistory(result);
            }
            resetGenerateButton();
        });
    }, 2000);
}

// RESET GENERATE BUTTON
function resetGenerateButton() {
    $('#generateBtn').prop('disabled', false).html('<i class="fas fa-magic"></i> Generate');
}

// LOAD VIRTUAL MODELS
function loadVirtualModels() {
    $('#virtualModelGrid').html(`
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-secondary);">
            <i class="fas fa-user" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
            <p>No virtual models available</p>
            <small>Generate virtual models first</small>
        </div>
    `);
}
</script>
@endpush
