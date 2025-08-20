<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Consultar Guia</title>
</head>
<body>
    <h1>Consultar Guia</h1>

    <form action="{{ route('consultar-guia') }}" method="GET">
        <input type="text" name="numero_guia" placeholder="Escribe el codigo de guia">
        <button type="submit">Buscar</button>
    </form>

    @if(isset($datosGuia) && $datosGuia)
        <h2>Resultados</h2>
        <pre>{{ print_r($datosGuia, true) }}</pre>
    @elseif(isset($datosGuia) && !$datosGuia)
        <p>No se encontraron datos para la guía proporcionada.</p>
    @endif
</body>
</html>