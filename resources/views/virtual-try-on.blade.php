<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>AI Virtual Try-On</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
    <h1 class="text-center mb-4">AI Virtual Try-On</h1>
    <form id="tryOnForm" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label>Modelo:</label>
            <select name="model_source" class="form-select">
                <option value="default">Default</option>
                <option value="upload">Upload</option>
            </select>
            <input type="file" name="human_image" class="form-control mt-2" accept=".jpg,.jpeg,.png">
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
$('#garmentType').change(function() {
    if ($(this).val() === 'multiple') {
        $('#singleGarment').addClass('d-none');
        $('#multipleGarments').removeClass('d-none');
    } else {
        $('#singleGarment').removeClass('d-none');
        $('#multipleGarments').addClass('d-none');
    }
});
$('#tryOnForm').on('submit', function(e) {
    e.preventDefault();
    let formData = new FormData(this);
    $('#generateBtn').prop('disabled', true);
    $('#resultsContainer').show();
    $('#gallery').html('<div class="col-12 text-center py-5"><p>Generando resultados...</p></div>');
    $.ajax({
        url: "{{ route('virtual-try-on.generate') }}",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.data && response.data.task_id) {
                checkTaskStatus(response.data.task_id);
            } else {
                $('#gallery').html('<div class="col-12 alert alert-danger">Error en la generación</div>');
                $('#generateBtn').prop('disabled', false);
            }
        },
        error: function(xhr) {
            $('#gallery').html('<div class="col-12 alert alert-danger">'+(xhr.responseJSON?.error || 'Error del servidor')+'</div>');
            $('#generateBtn').prop('disabled', false);
        }
    });
});
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
