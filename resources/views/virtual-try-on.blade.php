<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>AI Virtual Try-On</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* 🔥 NUEVO: Estilos para modelos default */
        .model-card {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .model-card:hover {
            border-color: #0d6efd;
            transform: translateY(-2px);
        }

        .model-card.selected {
            border-color: #0d6efd;
            background-color: #e7f3ff;
        }

        .model-image {
            height: 250px;
            object-fit: cover;
            object-position: center;
            width: 100%;
        }

        .model-radio {
            display: none;
        }

        .model-label {
            margin: 0;
            padding: 10px;
            text-align: center;
            font-weight: 500;
            color: #495057;
        }

        .model-card.selected .model-label {
            color: #0d6efd;
            font-weight: 600;
        }

        .selected-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #0d6efd;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .model-card.selected .selected-indicator {
            opacity: 1;
        }
    </style>
</head>
<body>
<div class="container py-4">
    <h1 class="text-center mb-4">AI Virtual Try-On</h1>
    <form id="tryOnForm" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label>Modelo:</label>
            <select name="model_source" class="form-select" id="modelSource">
                <option value="default">Default</option>
                <option value="upload">Upload</option>
            </select>

            <!-- 🔥 ARREGLADO: Selección de modelos default -->
            <div id="defaultModelSelection" class="mt-3">
                <label class="form-label">Selecciona un modelo:</label>
                <div class="row">
                    @forelse($defaultModels as $model)
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                            <div class="card model-card h-100" data-model="{{ $model['filename'] }}">
                                <div class="position-relative">
                                    <img src="{{ $model['url'] }}" class="model-image" alt="{{ $model['name'] }}">
                                    <div class="selected-indicator">
                                        <i class="fas fa-check"></i>✓
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <input class="model-radio" type="radio" name="selected_default_model" value="{{ $model['filename'] }}" id="model_{{ $loop->index }}">
                                    <label class="model-label w-100" for="model_{{ $loop->index }}">
                                        {{ $model['name'] }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-warning">
                                No hay modelos default disponibles. Coloca imágenes en <code>public/klingai/default_models/</code>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- 🔥 EXISTENTE: Upload de modelo -->
            <div id="uploadModelSection" class="mt-3" style="display: none;">
                <input type="file" name="human_image" class="form-control" accept=".jpg,.jpeg,.png">
            </div>
        </div>

        <div class="mb-3">
            <label>Tipo de prenda:</label>
            <select name="garment_type" class="form-select" id="garmentType">
                <option value="single">Single</option>
                <option value="multiple">Multiple</option>
            </select>
        </div>

        <div class="mb-3" id="singleGarment">
            <label>Prenda:</label>
            <input type="file" name="single_garment" class="form-control" accept=".jpg,.jpeg,.png">
        </div>

        <div class="mb-3 d-none" id="multipleGarments">
            <label>Top:</label>
            <input type="file" name="top_garment" class="form-control mb-2" accept=".jpg,.jpeg,.png">
            <label>Bottom:</label>
            <input type="file" name="bottom_garment" class="form-control" accept=".jpg,.jpeg,.png">
        </div>

        <div class="mb-3">
            <label>Cantidad de resultados:</label>
            <select name="output_count" class="form-select">
                @for ($i = 1; $i <= 4; $i++)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endfor
            </select>
        </div>

        <button type="submit" class="btn btn-primary w-100" id="generateBtn">Generar</button>
    </form>

    <div id="resultsContainer" class="mt-4" style="display:none;">
        <h4>Resultados</h4>
        <div id="gallery" class="row"></div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// 🔥 NUEVO: Manejar selección de modelos default
$(document).on('click', '.model-card', function() {
    // Remover selección anterior
    $('.model-card').removeClass('selected');

    // Agregar selección actual
    $(this).addClass('selected');

    // Marcar el radio button correspondiente
    const modelFilename = $(this).data('model');
    $(`input[value="${modelFilename}"]`).prop('checked', true);

    console.log('Modelo seleccionado:', modelFilename);
});

// 🔥 EXISTENTE: Manejar selección de tipo de modelo
$('#modelSource').change(function() {
    if ($(this).val() === 'upload') {
        $('#defaultModelSelection').hide();
        $('#uploadModelSection').show();
    } else {
        $('#defaultModelSelection').show();
        $('#uploadModelSection').hide();
        // Limpiar selección cuando cambie a default
        $('.model-card').removeClass('selected');
        $('input[name="selected_default_model"]').prop('checked', false);
    }
});

// 🔥 EXISTENTE: Manejar tipo de prenda
$('#garmentType').change(function() {
    if ($(this).val() === 'multiple') {
        $('#singleGarment').addClass('d-none');
        $('#multipleGarments').removeClass('d-none');
    } else {
        $('#singleGarment').removeClass('d-none');
        $('#multipleGarments').addClass('d-none');
    }
});

// 🔥 MEJORADO: Validación de formulario
$('#tryOnForm').on('submit', function(e) {
    e.preventDefault();

    // Validar modelo default seleccionado
    if ($('#modelSource').val() === 'default') {
        if (!$('input[name="selected_default_model"]:checked').length) {
            alert('Por favor selecciona un modelo default');
            return;
        }
    }

    // Validar prendas
    const garmentType = $('#garmentType').val();
    if (garmentType === 'single') {
        if (!$('input[name="single_garment"]')[0].files.length) {
            alert('Por favor selecciona una prenda');
            return;
        }
    } else {
        if (!$('input[name="top_garment"]')[0].files.length || !$('input[name="bottom_garment"]')[0].files.length) {
            alert('Por favor selecciona tanto el top como el bottom');
            return;
        }
    }

    let formData = new FormData(this);
    $('#generateBtn').prop('disabled', true);
    $('#resultsContainer').show();
    $('#gallery').html('<div class="col-12 text-center py-5"><p>Generando resultados...</p></div>');

    // 🔥 DEBUG: Verificar qué se está enviando
    console.log('Enviando formulario...');
    console.log('Modelo seleccionado:', $('input[name="selected_default_model"]:checked').val());
    console.log('Tipo de modelo:', $('#modelSource').val());

    $.ajax({
        url: "{{ route('virtual-try-on.generate') }}",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            console.log('Respuesta exitosa:', response);
            if (response.data && response.data.task_id) {
                checkTaskStatus(response.data.task_id);
            } else {
                $('#gallery').html('<div class="col-12 alert alert-danger">Error en la generación</div>');
                $('#generateBtn').prop('disabled', false);
            }
        },
        error: function(xhr) {
            console.log('Error en la petición:', xhr.responseJSON);
            $('#gallery').html('<div class="col-12 alert alert-danger">'+(xhr.responseJSON?.error || 'Error del servidor')+'</div>');
            $('#generateBtn').prop('disabled', false);
        }
    });
});

// 🔥 EXISTENTE: Polling de estado
function checkTaskStatus(taskId) {
    setTimeout(function poll() {
        $.get(`/virtual-try-on/status/${taskId}`, function(response) {
            if (response.data) {
                const status = response.data.task_status;
                if (status === 'succeed') {
                    displayResults(response.data.task_result.images);
                } else if (status === 'processing' || status === 'submitted') {
                    setTimeout(poll, 3000);
                } else {
                    $('#gallery').html('<div class="col-12 alert alert-danger">'+(response.data.task_status_msg || 'Task failed')+'</div>');
                    $('#generateBtn').prop('disabled', false);
                }
            } else {
                $('#gallery').html('<div class="col-12 alert alert-danger">Error checking status</div>');
                $('#generateBtn').prop('disabled', false);
            }
        }).fail(function() {
            $('#gallery').html('<div class="col-12 alert alert-danger">Error checking task status</div>');
            $('#generateBtn').prop('disabled', false);
        });
    }, 2000);
}

function displayResults(images) {
    $('#gallery').empty();
    images.forEach(image => {
        $('#gallery').append(`<div class="col-md-6"><img src="${image.url}" class="img-fluid mb-3"></div>`);
    });
    $('#generateBtn').prop('disabled', false);
}
</script>
</body>
</html>
