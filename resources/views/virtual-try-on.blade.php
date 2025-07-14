@extends('layouts.app')

@section('content')
<div class="main-container">
    <!-- LEFT PANEL -->
    <div class="left-panel">
        <!-- TABS -->
        <div class="sub-tabs">
            <button class="sub-tab" data-tab="virtual">Virtual Model</button>
            <button class="sub-tab active" data-tab="default">Default</button>
            <button class="sub-tab" data-tab="upload">Upload</button>
            <button class="info-btn" id="modelInfoBtn" title="Model Guidelines">
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

                                {{-- Marcar con dos campos: modelo e índice --}}
                                <input type="radio" name="selected_virtual_model" value="{{ $model->id }}" class="d-none">
                                <input type="radio" name="selected_virtual_index" value="{{ $index }}" class="d-none">

                                <div class="overlay">
                                    <div class="badges">
                                        <span class="badge {{ $model->gender }}">{{ ucfirst($model->gender) }}</span>
                                        <span class="badge">{{ ucfirst($model->age_group) }}</span>
                                        <span class="badge">{{ ucfirst($model->skin_tone) }}</span>
                                    </div>
                                    <div class="overlay-title">{{ $model->display_name }} - {{ $index + 1 }}</div>
                                    <div class="overlay-date">{{ $model->formatted_date }}</div>
                                </div>
                            </div>
                        @endforeach
                    @empty
                        <div class="no-images">
                            <i class="fas fa-user" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                            <p>No virtual models available</p>
                            <small>
                                <a href="{{ route('virtual-model') }}" style="color: var(--accent-2); text-decoration: none;">
                                    Generate virtual models first
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
                            <i class="fas fa-images" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                            <p>No hay modelos default disponibles</p>
                            <small>Coloca imágenes en public/klingai/default_models/</small>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- UPLOAD -->
            <div class="tab-content d-none" data-content="upload">
                <div class="upload" id="humanUploadArea" onclick="triggerFileInput('humanImageInput')">
                    <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                    <div class="upload-text">Upload Human Model</div>
                    <input type="file" id="humanImageInput" name="human_image" accept=".jpg,.jpeg,.png" class="file-input">
                    <div class="preview" id="humanPreview">
                        <img src="" alt="Preview">
                        <button class="remove-btn" onclick="clearFileInput('humanImageInput', 'humanUploadArea', 'humanPreview')">
                            <i class="fas fa-times"></i>
                        </button>
                        <button class="reupload-btn" onclick="triggerFileInput('humanImageInput')">
                            <i class="fas fa-upload"></i> Re-upload
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- GARMENT SECTION -->
        <div class="section">
            <!-- TABS -->
            <div class="tabs-container">
                <div class="tabs">
                    <button class="tab active" data-garment="single">Single Garment</button>
                    <button class="tab" data-garment="multiple">Multiple Garments</button>
                </div>
                <button class="info-btn" id="garmentInfoBtn" title="Garment Guidelines">
                    <i class="fas fa-info-circle"></i>
                </button>
            </div>

            <!-- SINGLE GARMENT -->
            <div class="garment-content" data-garment-content="single">
                <div class="upload" id="singleUploadArea" onclick="triggerFileInput('singleGarmentInput')">
                    <div class="upload-icon"><i class="fas fa-tshirt"></i></div>
                    <div class="upload-text">Upload Single Garment</div>
                    <input type="file" id="singleGarmentInput" name="single_garment" accept=".jpg,.jpeg,.png" class="file-input">
                    <div class="preview" id="singlePreview">
                        <img src="" alt="Preview">
                        <button class="remove-btn" onclick="clearFileInput('singleGarmentInput', 'singleUploadArea', 'singlePreview')">
                            <i class="fas fa-times"></i>
                        </button>
                        <button class="reupload-btn" onclick="triggerFileInput('singleGarmentInput')">
                            <i class="fas fa-upload"></i> Re-upload
                        </button>
                    </div>
                </div>
            </div>

            <!-- MULTIPLE GARMENTS -->
            <div class="garment-content d-none" data-garment-content="multiple">
                <div class="upload" id="topUploadArea" onclick="triggerFileInput('topGarmentInput')" style="margin-bottom: 12px;">
                    <div class="upload-icon"><i class="fas fa-tshirt"></i></div>
                    <div class="upload-text">Upload Top Garment</div>
                    <input type="file" id="topGarmentInput" name="top_garment" accept=".jpg,.jpeg,.png" class="file-input">
                    <div class="preview" id="topPreview">
                        <img src="" alt="Preview">
                        <button class="remove-btn" onclick="clearFileInput('topGarmentInput', 'topUploadArea', 'topPreview')">
                            <i class="fas fa-times"></i>
                        </button>
                        <button class="reupload-btn" onclick="triggerFileInput('topGarmentInput')">
                            <i class="fas fa-upload"></i> Re-upload
                        </button>
                    </div>
                </div>

                <div class="upload" id="bottomUploadArea" onclick="triggerFileInput('bottomGarmentInput')">
                    <div class="upload-icon"><i class="fas fa-tshirt"></i></div>
                    <div class="upload-text">Upload Bottom Garment</div>
                    <input type="file" id="bottomGarmentInput" name="bottom_garment" accept=".jpg,.jpeg,.png" class="file-input">
                    <div class="preview" id="bottomPreview">
                        <img src="" alt="Preview">
                        <button class="remove-btn" onclick="clearFileInput('bottomGarmentInput', 'bottomUploadArea', 'bottomPreview')">
                            <i class="fas fa-times"></i>
                        </button>
                        <button class="reupload-btn" onclick="triggerFileInput('bottomGarmentInput')">
                            <i class="fas fa-upload"></i> Re-upload
                        </button>
                    </div>
                </div>
            </div>

            <!-- 🔥 OUTPUT BLOQUEADO -->
            <select class="selector" name="output_count" disabled>
                <option value="1" selected>1 Output (Fixed)</option>
            </select>

            <!-- GENERATE -->
            <button class="btn-generate" id="generateBtn">
                <i class="fas fa-magic"></i> Generar
            </button>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right-panel">
        <div class="results-header">
            <i class="fas fa-images"></i>
            <span>Probador Virtual Resultados</span>
        </div>
        <div class="results-content">
            <div id="resultsContainer">
                <!-- Results will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- TOOLTIPS -->
<div class="tooltip" id="modelTooltip">
    <div class="tooltip-header">
        <h6><i class="fas fa-user"></i> Model Guidelines</h6>
        <button class="tooltip-close" onclick="hideTooltip('modelTooltip')">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="tooltip-content">
        <div class="tooltip-specs">
            <p><strong>Requirements:</strong></p>
            <ul>
                <li>Size: Up to 50MB</li>
                <li>Resolution: Short side ≥512px, Long side ≤4096px</li>
                <li>Formats: JPG/PNG</li>
            </ul>
        </div>
        <div class="guidelines-section">
            <h6 class="section-title valid">✓ Follow these guidelines for best results</h6>
            <div class="grid-6">
                @forelse($validModels as $model)
                    <div class="guideline-item">
                        <div class="guideline-image">
                            <img src="{{ $model['url'] }}" alt="{{ $model['description'] }}">
                        </div>
                        <div class="guideline-label valid">{{ $model['description'] }}</div>
                    </div>
                @empty
                    <p class="no-images">No valid models found</p>
                @endforelse
            </div>
        </div>
        <div class="guidelines-section">
            <h6 class="section-title invalid">✗ Avoid these examples</h6>
            <div class="grid-6">
                @forelse($invalidModels as $model)
                    <div class="guideline-item">
                        <div class="guideline-image">
                            <img src="{{ $model['url'] }}" alt="{{ $model['description'] }}">
                        </div>
                        <div class="guideline-label invalid">{{ $model['description'] }}</div>
                    </div>
                @empty
                    <p class="no-images">No invalid models found</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="tooltip" id="garmentTooltip">
    <div class="tooltip-header">
        <h6><i class="fas fa-tshirt"></i> Garment Guidelines</h6>
        <button class="tooltip-close" onclick="hideTooltip('garmentTooltip')">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="tooltip-content">
        <div class="tooltip-specs">
            <p><strong>Requirements:</strong></p>
            <ul>
                <li>Size: Up to 50MB</li>
                <li>Resolution: Short side ≥512px, Long side ≤4096px</li>
                <li>Formats: JPG/PNG</li>
            </ul>
        </div>
        <div class="guidelines-section">
            <h6 class="section-title valid">✓ Follow these guidelines</h6>
            <div class="grid-6">
                @forelse($validGarments as $garment)
                    <div class="guideline-item">
                        <div class="guideline-image">
                            <img src="{{ $garment['url'] }}" alt="{{ $garment['description'] }}">
                        </div>
                        <div class="guideline-label valid">{{ $garment['description'] }}</div>
                    </div>
                @empty
                    <p class="no-images">No valid garments found</p>
                @endforelse
            </div>
        </div>
        <div class="guidelines-section">
            <h6 class="section-title invalid">✗ Avoid these examples</h6>
            <div class="grid-6">
                @forelse($invalidGarments as $garment)
                    <div class="guideline-item">
                        <div class="guideline-image">
                            <img src="{{ $garment['url'] }}" alt="{{ $garment['description'] }}">
                        </div>
                        <div class="guideline-label invalid">{{ $garment['description'] }}</div>
                    </div>
                @empty
                    <p class="no-images">No invalid garments found</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- 🔥 MODAL PARA VER RESULTADOS -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="background: var(--bg-2); border: 1px solid var(--border);">
            <div class="modal-header" style="border-bottom: 1px solid var(--border);">
                <h5 class="modal-title" style="color: var(--txt-1);">
                    <i class="fas fa-images"></i> Result Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1);"></button>
            </div>
            <div class="modal-body p-0">
                <!-- IMAGE CONTAINER -->
                <div class="image-viewer-container" style="position: relative; height: 70vh; overflow: hidden; background: var(--bg-1);">
                    <img id="modalImage" src="" alt="Result Image" style="width: 100%; height: 100%; object-fit: contain; cursor: grab; transition: transform 0.3s;">
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
                                    <i class="fas fa-expand-arrows-alt"></i> Reset
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <!-- DOWNLOAD BUTTON -->
                            <button class="btn" style="background: var(--accent-1); border: none; color: white; padding: 8px 16px;" onclick="downloadImage()">
                                <i class="fas fa-download"></i> Download
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- HIDDEN FORM -->
<form id="hiddenForm" style="display: none;" enctype="multipart/form-data">
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

// 🔥 VARIABLES PARA ZOOM
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

    // TOOLTIPS
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

    // TABS
    $('.sub-tab').click(function() {
        const tab = $(this).data('tab');
        $('.sub-tab').removeClass('active');
        $(this).addClass('active');
        $('.tab-content').addClass('d-none');
        $(`[data-content="${tab}"]`).removeClass('d-none');
        $('#hiddenModelSource').val(tab);
        $('.item').removeClass('selected');
        $('input[name^="selected_"]').prop('checked', false);
        $('#hiddenSelectedModel, #hiddenSelectedVirtualModel').val('');
    });

    $('.tab').click(function() {
        const garment = $(this).data('garment');
        $('.tab').removeClass('active');
        $(this).addClass('active');
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
            // Default model
            $(this).find('input[name="selected_default_model"]').prop('checked', true);
            $('#hiddenSelectedModel').val(modelValue);
            $('#hiddenSelectedVirtualModel').val('');
            $('#hiddenSelectedVirtualIndex').remove(); // limpiar si existe
        } else {
            // Virtual model
            $(this).find('input[name="selected_virtual_model"]').prop('checked', true);
            $(this).find('input[name="selected_virtual_index"]').prop('checked', true);

            $('#hiddenSelectedVirtualModel').val(modelValue);

            // Crear o actualizar el campo hidden del índice
            if ($('#hiddenSelectedVirtualIndex').length === 0) {
                $('#hiddenForm').append(`<input type="hidden" name="selected_virtual_index" id="hiddenSelectedVirtualIndex" value="${modelIndex}">`);
            } else {
                $('#hiddenSelectedVirtualIndex').val(modelIndex);
            }

            $('#hiddenSelectedModel').val('');
        }
    });

    // 🔥 OUTPUT COUNT FIJO EN 1
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

        // Validation
        const validations = {
            default: () => !$('#hiddenSelectedModel').val() && alert('Please select a default model'),
            virtual: () => !$('#hiddenSelectedVirtualModel').val() && alert('Please select a virtual model'),
            upload: () => !$('#humanImageInput')[0].files.length && alert('Please upload a human model image')
        };

        if (validations[modelSource]?.()) return;

        if (garmentType === 'single' && !$('#singleGarmentInput')[0].files.length) {
            return alert('Please upload a garment');
        }

        if (garmentType === 'multiple' && (!$('#topGarmentInput')[0].files.length || !$('#bottomGarmentInput')[0].files.length)) {
            return alert('Please upload both top and bottom garments');
        }

        // PREPARE DATA
        const formData = new FormData();
        formData.append('_token', $('input[name="_token"]').val());
        formData.append('model_source', modelSource);
        formData.append('garment_type', garmentType);
        formData.append('output_count', '1'); // 🔥 SIEMPRE 1

        // MODEL DATA
        const modelActions = {
            default: () => formData.append('selected_default_model', $('#hiddenSelectedModel').val()),
            virtual: () => formData.append('selected_virtual_model', $('#hiddenSelectedVirtualModel').val()),
            upload: () => formData.append('human_image', $('#humanImageInput')[0].files[0])
        };
        modelActions[modelSource]();

        // GARMENT DATA
        if (garmentType === 'single') {
            formData.append('single_garment', $('#singleGarmentInput')[0].files[0]);
        } else {
            formData.append('top_garment', $('#topGarmentInput')[0].files[0]);
            formData.append('bottom_garment', $('#bottomGarmentInput')[0].files[0]);
        }

        // UI
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generating...');

        // TEMP RESULT
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

        // AJAX
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

// 🔥 ABRIR MODAL
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

// 🔥 ZOOM FUNCTION
const zoomImage = (factor) => {
    currentZoom *= factor;
    currentZoom = Math.max(0.5, Math.min(currentZoom, 5)); // Límites 50% - 500%
    updateImageTransform();
    $('#zoomLevel').text(Math.round(currentZoom * 100) + '%');
};

// 🔥 RESET ZOOM
const resetZoom = () => {
    currentZoom = 1;
    translateX = 0;
    translateY = 0;
    updateImageTransform();
    $('#zoomLevel').text('100%');
};

// 🔥 UPDATE TRANSFORM
const updateImageTransform = () => {
    $('#modalImage').css('transform', `translate(${translateX}px, ${translateY}px) scale(${currentZoom})`);
};

// 🔥 DOWNLOAD IMAGE
const downloadImage = () => {
    if (!currentImageSrc) return;

    const link = document.createElement('a');
    link.href = currentImageSrc;
    link.download = `virtual-try-on-result-${Date.now()}.png`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

// 🔥 DRAG FUNCTIONALITY
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

// 🔥 WHEEL ZOOM
$('#modalImage').on('wheel', function(e) {
    e.preventDefault();
    const factor = e.originalEvent.deltaY > 0 ? 0.9 : 1.1;
    zoomImage(factor);
});

// UTILITIES
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
        if (top + 450 > viewportHeight) top = rect.top - 460;
        if (top < 10) top = (viewportHeight - 450) / 2;
        tooltip.style.left = left + 'px';
        tooltip.style.top = top + 'px';
    }
};

const hideTooltip = tooltipId => document.getElementById(tooltipId).style.display = 'none';

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

const showEmptyState = () => {
    $('#resultsContainer').html(`
        <div class="text-center" style="padding: 60px 20px; color: var(--txt-2);">
            <i class="fas fa-tshirt" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
            <p>No results yet</p>
            <small>Complete the form to generate your first try-on</small>
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

        html += `
            <div class="result-group">
                <div class="result-header">
                    <div class="result-info">
                        <div><strong> ${'Modelos Virtual Try On Generados' || 'Generando...'}</strong></div>
                        <div>${date}</div>
                        <div>Modelo: ${result.model_type} | Prenda/s: ${result.garments_type}</div>
                    </div>
                    <div class="${statusClass}">${result.status.toUpperCase()}</div>
                </div>
                <div class="grid-4">`;

        if (result.result_image_paths?.length) {
            result.result_image_paths.forEach((path, imgIndex) => {
                // 🔥 AGREGAR CLICK PARA ABRIR MODAL
                html += `<div class="item" style="cursor: pointer; position: relative;" onclick="openImageModal('${path}', 'Resultado ${imgIndex + 1}')">
                    <img src="${path}" alt="Resultado ${imgIndex + 1}">
                    <div class="image-overlay" style="position: absolute; top: 8px; right: 8px; background: rgba(0,0,0,0.7); border-radius: 4px; padding: 4px 8px; opacity: 0; transition: opacity 0.3s;">
                        <i class="fas fa-search-plus" style="color: white; font-size: 12px;"></i>
                    </div>
                </div>`;
            });
        } else if (result.status === 'processing') {
            html += `<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--txt-2);">
                <div class="spinner-border text-primary" role="status"></div>
                <p style="margin-top: 16px;">Processing...</p>
                <small>⏳ Tiempo estimado: 5–20 segundos.</small>
            </div>`;
        } else {
            html += `<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--txt-2);">
                <i class="fas fa-exclamation-circle" style="font-size: 32px; margin-bottom: 16px; opacity: 0.5;"></i>
                <p>No results available</p>
            </div>`;
        }

        html += `</div></div>`;
    });

    container.html(html);

    // 🔥 AGREGAR HOVER EFFECT PARA OVERLAY
    $('.item').hover(
        function() { $(this).find('.image-overlay').css('opacity', '1'); },
        function() { $(this).find('.image-overlay').css('opacity', '0'); }
    );
};

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
    $('#generateBtn').prop('disabled', false).html('<i class="fas fa-magic"></i> Generate');
};
</script>
@endpush
