@extends('layouts.probador-virtual-ia')

@section('content')
<div class="main-container d-flex flex-wrap flex-row">
    <!-- LEFT PANEL -->
    <div class="col-12 col-lg-4 d-flex flex-column overflow-hidden px-2" style="align-items:stretch">
        <div class="card">
            <div class="card-header">
                <span class="d-block"><b>Configuración de Video</b></span>
            </div>
            <div class="card-body">
                <!-- IMAGES SECTION -->
                <div class="section mt-0">
                    <div class="title d-flex align-items-center">
                        <i class="fas fa-images mr-2"></i>
                        <span>Imágenes de Entrada</span>
                        <button class="btn btn-outline-info btn-sm info-btn ml-auto" id="imagesInfoBtn" title="Guía de Imágenes">
                            <i class="fas fa-info-circle"></i>
                        </button>
                    </div>

                    <!-- MULTIPLE IMAGE UPLOAD -->
                    <div class="group">
                        <label class="label">Subir Imágenes (Máximo 4)</label>
                        <div id="imageUploadsContainer">
                            <div class="upload" id="imageUploadArea1">
                                <!-- Nuevo botón dropdown -->
                                <div class="dropdown">
                                    <button class="btn btn-upload dropdown-toggle w-100 h-100" type="button" data-toggle="dropdown" aria-expanded="false">
                                        <div class="upload-content">
                                            <div class="upload-icon"><i class="fas fa-image"></i></div>
                                            <div class="upload-text">Imagen 1 (Requerida)</div>
                                        </div>
                                    </button>
                                    <div class="dropdown-menu w-100">
                                        <button class="dropdown-item" onclick="triggerFileInput('imageInput1')">
                                            <i class="fas fa-upload mr-2"></i> Subir desde equipo
                                        </button>
                                        <button class="dropdown-item" onclick="showTryOnModal(1)">
                                            <i class="fas fa-images mr-2"></i> Usar imagen del probador
                                        </button>
                                    </div>
                                </div>

                                <input type="file" id="imageInput1" name="images[]" accept=".jpg,.jpeg,.png" class="file-input">
                                <div class="preview" id="preview1">
                                    <img src="" alt="Preview">
                                    <button class="btn btn-danger btn-sm remove-btn" onclick="clearFileInput('imageInput1', 'imageUploadArea1', 'preview1')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <button class="btn btn-primary btn-sm reupload-btn" onclick="$('#imageUploadArea1 .dropdown-toggle').dropdown('toggle')">
                                        <i class="fas fa-upload"></i> Re-subir
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Agregar el modal de selección de imágenes Try-On -->
                    <div class="modal fade" id="tryOnImagesModal" tabindex="-1">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-images"></i> Seleccionar Imagen del Probador Virtual
                                    </h5>
                                    <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="grid-4" id="tryOnImagesGrid">
                                        @foreach($virtualTryOns as $tryOn)
                                            @foreach($tryOn->result_image_paths as $path)
                                                <div class="item" onclick="selectTryOnImage('{{ Storage::url($path) }}', currentUploadIndex)">
                                                    <img src="{{ Storage::url($path) }}" alt="Try-On Result">
                                                    <div class="image-overlay">
                                                        <i class="fas fa-check"></i>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PROMPT SECTION -->
                    <div class="section">
                        <div class="title d-flex align-items-center">
                            <i class="fas fa-edit mr-2"></i>
                            <span>Descripción del Video</span>
                        </div>

                        <div class="group">
                            <label class="label">Prompt Principal</label>
                            <div class="auto-textarea-container">
                                <textarea id="promptText" class="form-control auto-textarea" rows="2"
                                    placeholder="Describe cómo deseas que sea la transición entre las imágenes..."
                                    maxlength="2500"></textarea>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="text-muted char-count">0/2500</small>
                            </div>
                        </div>

                        <div class="group">
                            <label class="label">Prompt Negativo (Opcional)</label>
                            <div class="auto-textarea-container">
                                <textarea id="negativePromptText" class="form-control auto-textarea" rows="2"
                                    placeholder="Describe lo que NO deseas que aparezca en el video..."
                                    maxlength="2500"></textarea>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="text-muted negative-char-count">0/2500</small>
                            </div>
                        </div>
                    </div>

                    <!-- VIDEO SETTINGS -->
                    <div class="section">
                        <div class="title d-flex align-items-center">
                            <i class="fas fa-cogs mr-2"></i>
                            <span>Configuración del Video</span>
                        </div>

                        <!-- MODE -->
                        <div class="group">
                            <label class="label">Modo de Generación</label>
                            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                <label class="btn btn-outline-primary active">
                                    <input type="radio" name="mode" value="std" checked>
                                    <i class="fas fa-bolt"></i> Estándar
                                </label>
                                <label class="btn btn-outline-primary">
                                    <input type="radio" name="mode" value="pro">
                                    <i class="fas fa-star"></i> Profesional
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">
                                Pro: Mayor calidad pero toma más tiempo
                            </small>
                        </div>

                        <!-- DURATION -->
                        <div class="group">
                            <label class="label">Duración del Video</label>
                            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                <label class="btn btn-outline-info active">
                                    <input type="radio" name="duration" value="5" checked>5 segundos
                                </label>
                                <label class="btn btn-outline-info">
                                    <input type="radio" name="duration" value="10">10 segundos
                                </label>
                            </div>
                        </div>

                        <!-- ASPECT RATIO -->
                        <div class="group">
                            <label class="label">Relación de Aspecto</label>
                            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                <label class="btn btn-outline-warning active">
                                    <input type="radio" name="aspect_ratio" value="16:9" checked>16:9
                                </label>
                                <label class="btn btn-outline-warning">
                                    <input type="radio" name="aspect_ratio" value="9:16">9:16
                                </label>
                                <label class="btn btn-outline-warning">
                                    <input type="radio" name="aspect_ratio" value="1:1">1:1
                                </label>
                            </div>
                        </div>

                        <!-- GENERATE BUTTON -->
                        <button class="btn btn-success btn-block mt-4" id="generateBtn">
                            <i class="fas fa-video"></i> Generar Video
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="col-12 col-lg-8 d-flex flex-column overflow-hidden px-2" style="align-items:stretch">
        <div class="card">
            <div class="card-header">
                <div>
                    <i class="fas fa-film"></i>
                    <span><b>Videos Generados</b></span>
                </div>
            </div>
            <div class="card-body">
                <div id="resultsContainer">
                    <!-- Results will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TOOLTIP IMAGES -->
<div class="tooltip model-guidelines" id="imagesToolTip">
    <div class="tooltip-header bg-light border-bottom-dark d-flex justify-content-between align-items-center p-3">
        <h6 class="mb-0 text-dark d-flex align-items-center">
            <i class="fas fa-info-circle mr-2"></i> Guía de Imágenes
        </h6>
        <button class="btn btn-outline-danger btn-sm" onclick="hideTooltip('imagesToolTip')">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="tooltip-content p-3">
        <p class="text-dark mb-3">
            <strong>Por favor siga estas pautas para subir imágenes y lograr los mejores resultados:</strong>
        </p>
        <div class="alert alert-info py-2 px-3 mb-4">
            <ul class="mb-0">
                <li>Tamaño máximo por imagen: 10MB</li>
                <li>Dimensiones mínimas: 300x300 píxeles</li>
                <li>Relación de aspecto: entre 1:2.5 y 2.5:1</li>
                <li>Formatos permitidos: JPG/JPEG/PNG</li>
                <li>Máximo 4 imágenes</li>
            </ul>
        </div>
    </div>
</div>

<!-- MODAL PARA VER VIDEO -->
<div class="modal fade" id="videoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-film"></i> Vista previa del video
                </h5>
                <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="video-container">
                    <video id="modalVideo" controls>
                        <source src="" type="video/mp4">
                        Tu navegador no soporta la reproducción de videos.
                    </video>
                </div>
                <div class="video-controls">
                    <div class="row align-items-center">
                        <div class="col-md-12 text-right">
                            <button class="btn btn-success" onclick="downloadVideo()">
                                <i class="fas fa-download"></i> Descargar Video
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- HIDDEN FORM -->
<form id="hiddenForm">
    @csrf
</form>

<script>
window.existingVideos = @json($existingVideos);
</script>
@endsection

@push('scripts')
<script>
let videoHistory = [];
let currentVideoUrl = '';
let maxImages = 4;
let currentImageCount = 1;

$(document).ready(function() {
    initializeExistingData();
    initializeTooltips();
    initializeImageUploads();
    initializeTextareas();
    initializeGeneration();
});

function initializeExistingData() {
    if (window.existingVideos?.length) {
        videoHistory = window.existingVideos.map(video => ({
            id: video.id,
            task_id: video.task_id,
            prompt: video.prompt,
            negative_prompt: video.negative_prompt,
            mode: video.mode,
            duration: video.duration,
            aspect_ratio: video.aspect_ratio,
            status: video.status,
            created_at: video.created_at,
            input_image_paths: video.input_image_paths || [],
            result_video_paths: video.result_video_paths || []
        }));
        renderVideoHistory();
    } else {
        showEmptyState();
    }
}

function initializeTooltips() {
    $('#imagesInfoBtn').click(e => {
        e.stopPropagation();
        toggleTooltip('imagesToolTip', e.target);
    });

    $(document).click(() => {
        hideTooltip('imagesToolTip');
    });

    $('.tooltip').click(e => {
        e.stopPropagation();
    });
}

function initializeImageUploads() {
    $('#imageInput1').change(function() {
        handleImageUpload(this, 1);
    });
}

function handleImageUpload(input, index) {
    if (input.files && input.files[0]) {
        validateAndPreviewImage(input.files[0], index, () => {
            if (index === currentImageCount && currentImageCount < maxImages) {
                addNewImageUpload();
            }
        });
    }
}

function validateAndPreviewImage(file, index, callback) {
    // Validar tamaño
    if (file.size > 10 * 1024 * 1024) {
        Swal.fire({
            icon: 'error',
            title: 'Archivo muy grande',
            text: 'La imagen no debe superar los 10MB'
        });
        return;
    }

    // Validar formato
    if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
        Swal.fire({
            icon: 'error',
            title: 'Formato no soportado',
            text: 'Solo se permiten archivos JPG y PNG'
        });
        return;
    }

    // Validar dimensiones
    const img = new Image();
    img.onload = function() {
        const width = this.width;
        const height = this.height;
        const ratio = width / height;

        if (width < 300 || height < 300) {
            Swal.fire({
                icon: 'error',
                title: 'Imagen muy pequeña',
                text: 'Las dimensiones mínimas son 300x300 píxeles'
            });
            return;
        }

        if (ratio < 0.4 || ratio > 2.5) {
            Swal.fire({
                icon: 'error',
                title: 'Relación de aspecto inválida',
                text: 'La relación de aspecto debe estar entre 1:2.5 y 2.5:1'
            });
            return;
        }

        showImagePreview(file, index);
        if (callback) callback();
    };
    img.src = URL.createObjectURL(file);
}

function showImagePreview(file, index) {
    const reader = new FileReader();
    reader.onload = e => {
        const preview = document.getElementById(`preview${index}`);
        const uploadArea = document.getElementById(`imageUploadArea${index}`);
        preview.querySelector('img').src = e.target.result;
        preview.style.display = 'block';
        uploadArea.classList.add('has-file');
        uploadArea.querySelector('.upload-icon').style.display = 'none';
        uploadArea.querySelector('.upload-text').style.display = 'none';
    };
    reader.readAsDataURL(file);
}

// Función para mostrar el modal de imágenes Try-On
function showTryOnModal(index) {
    currentUploadIndex = index;
    $('#tryOnImagesModal').modal('show');
}

// Función para seleccionar una imagen del Try-On
function selectTryOnImage(imageUrl, index) {
    // Crear un objeto File a partir de la URL
    fetch(imageUrl)
        .then(res => res.blob())
        .then(blob => {
            const file = new File([blob], `tryon_${Date.now()}.jpg`, { type: 'image/jpeg' });

            // Validar y mostrar la imagen
            validateAndPreviewImage(file, index, () => {
                if (index === currentImageCount && currentImageCount < maxImages) {
                    addNewImageUpload();
                }
            });

            // Asignar el archivo al input correspondiente
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            document.getElementById(`imageInput${index}`).files = dataTransfer.files;
        });

    // Cerrar el modal
    $('#tryOnImagesModal').modal('hide');
}

function addNewImageUpload() {
    currentImageCount++;
    const newIndex = currentImageCount;

    const template = `
        <div class="upload mt-3" id="imageUploadArea${newIndex}">
            <div class="dropdown">
                <button class="btn btn-upload dropdown-toggle w-100 h-100" type="button" data-toggle="dropdown">
                    <div class="upload-content">
                        <div class="upload-icon"><i class="fas fa-image"></i></div>
                        <div class="upload-text">Imagen ${newIndex} (Opcional)</div>
                    </div>
                </button>
                <div class="dropdown-menu w-100">
                    <button class="dropdown-item" onclick="triggerFileInput('imageInput${newIndex}')">
                        <i class="fas fa-upload mr-2"></i> Subir desde equipo
                    </button>
                    <button class="dropdown-item" onclick="showTryOnModal(${newIndex})">
                        <i class="fas fa-images mr-2"></i> Usar imagen del probador
                    </button>
                </div>
            </div>
            <input type="file" id="imageInput${newIndex}" name="images[]" accept=".jpg,.jpeg,.png" class="file-input">
            <div class="preview" id="preview${newIndex}">
                <img src="" alt="Preview">
                <button class="btn btn-danger btn-sm remove-btn" onclick="clearFileInput('imageInput${newIndex}', 'imageUploadArea${newIndex}', 'preview${newIndex}')">
                    <i class="fas fa-times"></i>
                </button>
                <button class="btn btn-primary btn-sm reupload-btn" onclick="$('#imageUploadArea${newIndex} .dropdown-toggle').dropdown('toggle')">
                    <i class="fas fa-upload"></i> Re-subir
                </button>
            </div>
        </div>
    `;

    $('#imageUploadsContainer').append(template);

    $(`#imageInput${newIndex}`).change(function() {
        handleImageUpload(this, newIndex);
    });
}

function initializeTextareas() {
    $('#promptText, #negativePromptText').on('input', function() {
        const count = $(this).val().length;
        const countElement = $(this).closest('.group').find('.char-count, .negative-char-count');
        countElement.text(`${count}/2500`);

        adjustTextareaHeight(this);
    });
}

function adjustTextareaHeight(textarea) {
    textarea.style.height = 'auto';
    const scrollHeight = textarea.scrollHeight;
    const newHeight = Math.min(Math.max(scrollHeight, 48), 200);
    textarea.style.height = newHeight + 'px';
    textarea.style.overflowY = scrollHeight > 200 ? 'auto' : 'hidden';
}

function initializeGeneration() {
    $('#generateBtn').click(function() {
        if (!validateForm()) return;

        const formData = new FormData();
        formData.append('_token', $('input[name="_token"]').val());

        // Agregar imágenes
        let hasFiles = false;
        for (let i = 1; i <= currentImageCount; i++) {
            const input = document.getElementById(`imageInput${i}`);
            if (input && input.files[0]) {
                formData.append('images[]', input.files[0]);
                hasFiles = true;
            }
        }

        if (!hasFiles) {
            Swal.fire({
                icon: 'warning',
                title: 'Imágenes requeridas',
                text: 'Por favor sube al menos una imagen'
            });
            return;
        }

        // Agregar configuración
        formData.append('prompt', $('#promptText').val());
        formData.append('negative_prompt', $('#negativePromptText').val());
        formData.append('mode', $('input[name="mode"]:checked').val());
        formData.append('duration', $('input[name="duration"]:checked').val());
        formData.append('aspect_ratio', $('input[name="aspect_ratio"]:checked').val());

        submitGeneration(formData);
    });
}

function validateForm() {
    const prompt = $('#promptText').val().trim();
    if (!prompt) {
        Swal.fire({
            icon: 'warning',
            title: 'Prompt requerido',
            text: 'Por favor ingresa una descripción para el video'
        });
        return false;
    }
    return true;
}

function submitGeneration(formData) {
    $('#generateBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generando...');

    const tempResult = {
        id: 'temp_' + Date.now(),
        prompt: $('#promptText').val(),
        negative_prompt: $('#negativePromptText').val(),
        mode: $('input[name="mode"]:checked').val(),
        duration: $('input[name="duration"]:checked').val(),
        aspect_ratio: $('input[name="aspect_ratio"]:checked').val(),
        status: 'processing',
        created_at: new Date().toISOString(),
        task_id: null,
        input_image_paths: [],
        result_video_paths: []
    };

    addVideoToHistory(tempResult);

    $.ajax({
        url: "{{ route('image-to-video.generate') }}",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: response => {
            if (response.data?.task_id) {
                tempResult.task_id = response.data.task_id;
                updateVideoInHistory(tempResult);
                checkVideoStatus(response.data.task_id);
            } else {
                tempResult.status = 'failed';
                updateVideoInHistory(tempResult);
                resetGenerateButton();
            }
        },
        error: xhr => {
            tempResult.status = 'failed';
            updateVideoInHistory(tempResult);
            resetGenerateButton();

            Swal.fire({
                icon: 'error',
                title: 'Error de generación',
                text: xhr.responseJSON?.error || 'Error del servidor'
            });
        }
    });
}

function checkVideoStatus(taskId) {
    setTimeout(function poll() {
        $.get(`/image-to-video/status/${taskId}`)
            .done(response => {
                if (response.data) {
                    const status = response.data.task_status;
                    if (status === 'succeed') {
                        const video = videoHistory.find(v => v.task_id === taskId);
                        if (video) {
                            video.status = 'completed';
                            video.result_video_paths = response.data.local_videos || [];
                            updateVideoInHistory(video);
                        }
                        resetGenerateButton();
                    } else if (status === 'processing' || status === 'submitted') {
                        setTimeout(poll, 3000);
                    } else {
                        const video = videoHistory.find(v => v.task_id === taskId);
                        if (video) {
                            video.status = 'failed';
                            updateVideoInHistory(video);
                        }
                        resetGenerateButton();
                    }
                }
            })
            .fail(() => {
                const video = videoHistory.find(v => v.task_id === taskId);
                if (video) {
                    video.status = 'failed';
                    updateVideoInHistory(video);
                }
                resetGenerateButton();
            });
    }, 2000);
}

function addVideoToHistory(video) {
    videoHistory.unshift(video);
    renderVideoHistory();
}

function updateVideoInHistory(updatedVideo) {
    const index = videoHistory.findIndex(v => v.id === updatedVideo.id || v.task_id === updatedVideo.task_id);
    if (index !== -1) {
        videoHistory[index] = { ...videoHistory[index], ...updatedVideo };
        renderVideoHistory();
    }
}

function renderVideoHistory() {
    const container = $('#resultsContainer');
    if (videoHistory.length === 0) return showEmptyState();

    let html = '';
    videoHistory.forEach(video => {
        const statusClass = `status ${video.status}`;
        const date = new Date(video.created_at).toLocaleString();

        html += `
            <div class="result-group">
                <div class="result-header">
                    <div class="result-info">
                        <div><b>Fecha y hora: </b>${date}</div>
                        <div><b>Modo: </b>${video.mode === 'std' ? 'Estándar' : 'Profesional'} |
                             <b>Duración: </b>${video.duration}s |
                             <b>Aspecto: </b>${video.aspect_ratio}</div>
                        <div class="prompt-used"><b>Prompt: </b>${video.prompt}</div>
                        ${video.negative_prompt ? `<div class="prompt-used"><b>Prompt Negativo: </b>${video.negative_prompt}</div>` : ''}
                    </div>
                    <div class="${statusClass}">${video.status === 'completed' ? 'COMPLETADO' :
                                                video.status === 'processing' ? 'EN PROCESO' : 'FALLIDO'}</div>
                </div>`;

        if (video.status === 'completed' && video.result_video_paths?.length) {
            html += `<div class="video-grid">`;
            video.result_video_paths.forEach(path => {
                const videoUrl = `/storage/${path.replace(/\\/g, '/')}`;
                html += `
                    <div class="video-item" onclick="openVideoModal('${videoUrl}')">
                        <video class="preview-video">
                            <source src="${videoUrl}" type="video/mp4">
                        </video>
                        <div class="video-overlay">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>`;
            });
            html += `</div>`;
        } else if (video.status === 'processing') {
            html += `
                <div style="text-align: center; padding: 40px;">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3">Generando video...</p>
                    <small>⏳ Tiempo estimado: 30-60 segundos.</small>
                </div>`;
        } else {
            html += `
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-exclamation-circle fa-2x text-muted mb-3"></i>
                    <p class="text-muted">No hay resultados disponibles</p>
                </div>`;
        }

        html += `</div>`;
    });

    container.html(html);

    // Inicializar videos de preview
    $('.preview-video').each(function() {
        this.addEventListener('mouseover', function() { this.play(); });
        this.addEventListener('mouseout', function() { this.pause(); this.currentTime = 0; });
        this.muted = true;
    });
}

function showEmptyState() {
    $('#resultsContainer').html(`
        <div class="text-center">
            <i class="fas fa-film fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Aún no hay videos generados</h5>
            <p class="text-muted">Sube tus imágenes y genera tu primer video</p>
        </div>
    `);
}

function openVideoModal(videoUrl) {
    currentVideoUrl = videoUrl;
    $('#modalVideo source').attr('src', videoUrl);
    $('#modalVideo')[0].load();
    $('#videoModal').modal('show');
}

function downloadVideo() {
    if (!currentVideoUrl) return;
    const link = document.createElement('a');
    link.href = currentVideoUrl;
    link.download = `video-${Date.now()}.mp4`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function resetGenerateButton() {
    $('#generateBtn')
        .prop('disabled', false)
        .html('<i class="fas fa-video"></i> Generar Video');
}

// Utilidades
function triggerFileInput(inputId) {
    event.stopPropagation();
    document.getElementById(inputId).click();
}

function clearFileInput(inputId, areaId, previewId) {
    event.stopPropagation();
    const input = document.getElementById(inputId);
    const area = document.getElementById(areaId);
    const preview = document.getElementById(previewId);

    input.value = '';
    preview.style.display = 'none';
    area.classList.remove('has-file');
    area.querySelector('.upload-icon').style.display = 'block';
    area.querySelector('.upload-text').style.display = 'block';
}
</script>

<style>
/* Estilos específicos para video */
.video-container {
    position: relative;
    width: 100%;
    background: #000;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 400px;
}

#modalVideo {
    max-width: 100%;
    max-height: 70vh;
}

.video-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    padding: 1rem;
}

.video-item {
    position: relative;
    aspect-ratio: 16/9;
    cursor: pointer;
    border-radius: 8px;
    overflow: hidden;
    background: #000;
}

.preview-video {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.video-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity 0.3s;
}

.video-overlay i {
    color: white;
    font-size: 2rem;
}

.video-item:hover .video-overlay {
    opacity: 1;
}

.video-controls {
    padding: 1rem;
    background: var(--bg-3);
    border-top: 1px solid var(--border);
}

.btn-upload {
    background: none;
    border: 2px dashed var(--border);
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    transition: all 0.3s;
}

.btn-upload:hover, .btn-upload:focus {
    border-color: var(--accent-2);
    background: rgba(59, 130, 246, 0.1);
}

.upload-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.dropdown-menu {
    padding: 0;
    border-radius: 8px;
    box-shadow: var(--shadow-medium);
}

.dropdown-item {
    padding: 12px 16px;
    transition: all 0.2s;
}

.dropdown-item:hover {
    background: var(--accent-2);
    color: white;
}

#tryOnImagesGrid .item {
    cursor: pointer;
    transition: all 0.3s;
}

#tryOnImagesGrid .item:hover {
    transform: scale(1.05);
    box-shadow: var(--shadow-medium);
}

#tryOnImagesGrid .image-overlay {
    background: rgba(59, 130, 246, 0.8);
    opacity: 0;
}

#tryOnImagesGrid .item:hover .image-overlay {
    opacity: 1;
}

</style>
@endpush
