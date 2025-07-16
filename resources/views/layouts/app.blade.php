<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'AI Virtual Try-On') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- 🍯 SWEETALERT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --bg-1: #FFFFF0; --bg-2: #f8f8f8   ; --bg-3: #FFFFF0;
            --txt-1: #222222; --txt-2: #0c0c0c;
            --accent-1: #3b82f6; --accent-2: #3b82f6;
            --border: rgb(177, 177, 177);
            --shadow-light: 0 0 0 2px rgba(59, 130, 246, 0.3);
            --shadow-medium: 0 0 0 3px rgba(59, 130, 246, 0.5);
            --shadow-heavy: 0 8px 32px rgba(0, 0, 0, 0.5);
        }

        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; background: var(--bg-1); color: var(--txt-1); }

        /* 🔥 NAVBAR */
        .navbar-custom { background: #E7EBDC !important; border-bottom: 1px solid var(--border); padding: 12px 0; }
        .navbar-brand { color: var(--txt-1) !important; font-weight: 600; font-size: 1.25rem; }
        .navbar-brand:hover { color: var(--accent-2) !important; }
        .nav-link { color: var(--txt-2) !important; font-weight: 500; padding: 8px 16px !important; border-radius: 6px; transition: all 0.3s; }
        .nav-link:hover { color: #D3D3D3 !important; background: #545b62; }
        .nav-link.active { color: var(--txt-1) !important; background: var(--accent-2); }

        /* 🔥 LAYOUT */
        .main-content { padding: 0; margin: 0; width: 100%; height: calc(100vh - 70px); overflow: hidden; }
        .main-container { display: flex; height: 100%; overflow: hidden; }
        .left-panel { width: 520px; background: var(--bg-2); border-right: 1px solid var(--border); display: flex; flex-direction: column; overflow: hidden; }
        .right-panel { flex: 1; background: var(--bg-1); display: flex; flex-direction: column; overflow: hidden; }

        /* 🔥 TABS RESTAURADOS */
        .sub-tabs { display: flex; background: var(--bg-3); border-bottom: 1px solid var(--border); align-items: center; padding-right: 12px; }
        .sub-tab {
            flex: 1;
            padding: 12px;
            text-align: center;
            background: transparent;
            border: none;
            color: var(--txt-2);
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s;
            border-radius: 0;
        }
        .sub-tab.active { background: var(--bg-2); color: var(--txt-1); }
        .sub-tab:hover { background: var(--bg-1); color: var(--txt-1); }

        /* 🔥 TABS GARMENT RESTAURADOS */
        .tabs-container { display: flex; align-items: center; margin-bottom: 16px; }
        .tabs { flex: 1; display: flex; }
        .tab {
            flex: 1;
            padding: 8px 12px;
            background: transparent;
            border: 1px solid var(--border);
            color: var(--txt-2);
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s;
            border-radius: 0;
        }
        .tab:first-child { border-radius: 6px 0 0 6px; }
        .tab:last-child { border-radius: 0 6px 6px 0; border-left: none; }
        .tab.active { background: var(--accent-2); color: white; border-color: var(--accent-2); }
        .tab:hover { background: var(--bg-1); color: var(--txt-1); }

        /* 🔥 BOTONES INFO RESTAURADOS */
        .info-btn {
            width: 28px;
            height: 28px;
            background: transparent;
            border: 1px solid var(--border);
            color: var(--txt-2);
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 8px;
            font-size: 12px;
            flex-shrink: 0;
        }
        .info-btn:hover { background: var(--accent-2); color: white; border-color: var(--accent-2); }

        /* 🔥 CONTENEDORES */
        .scroll-container {overflow-y: scroll; scrollbar-width: none; -ms-overflow-style: none; padding: 10px;}
        .scroll-container::-webkit-scrollbar {display: none;}
        .section { border-bottom: 1px solid var(--border); padding: 20px; }
        .section:last-child { border-bottom: none; }
        .group { margin-bottom: 20px; }
        .group:last-child { margin-bottom: 0; }

        /* 🔥 LABELS */
        .label { display: block; color: var(--txt-1); font-size: 13px; font-weight: 600; margin-bottom: 8px; }
        .title { color: var(--txt-1); margin-bottom: 16px; font-size: 14px; }

        /* 🔥 GRIDS */
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
        .grid-6 { display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px; margin-bottom: 0; }

        /* 🔥 BOTONES COLOR */
        .color-btn { width: 60px; height: 40px; border: 2px solid var(--border); border-radius: 8px; position: relative; flex: none; }
        .color-btn.active { border-color: var(--accent-2); box-shadow: var(--shadow-light); }
        .color-check { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: rgba(0, 0, 0, 0.7); font-size: 14px; opacity: 0; transition: opacity 0.3s; }
        .color-btn.active .color-check { opacity: 1; }

        /* 🔥 ITEMS MODELO */
        .item { aspect-ratio: 3/4; background: var(--bg-3); border: 3px solid transparent; border-radius: 8px; overflow: hidden; cursor: pointer; transition: all 0.3s; position: relative; min-height: 140px; }
        .item:hover { border-color: var(--accent-2); box-shadow: var(--shadow-light); }
        .item.selected { border-color: var(--accent-2); border-width: 4px; box-shadow: var(--shadow-medium); }
        .item img { width: 100%; height: 100%; object-fit: cover; }

        /* 🔥 OVERLAYS */
        .overlay { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8)); padding: 8px; opacity: 0; transition: opacity 0.3s; }
        .item:hover .overlay { opacity: 1; }
        .badges { display: flex; gap: 4px; margin-bottom: 4px; flex-wrap: wrap; }
        .badge.male { background: rgba(59, 130, 246, 0.8); }
        .badge.female { background: rgba(236, 72, 153, 0.8); }
        .overlay-title { color: white; font-size: 10px; font-weight: 600; margin-bottom: 2px; }
        .overlay-date { color: rgba(255, 255, 255, 0.7); font-size: 9px; }

        /* 🔥 UPLOAD */
        .upload { border: 2px dashed var(--border); border-radius: 8px; padding: 15px; text-align: center; background: var(--bg-3); margin-bottom: 16px; cursor: pointer; transition: all 0.3s; position: relative; min-height: 80px; }
        .upload:hover { border-color: var(--accent-2); background: rgba(59, 130, 246, 0.1); }
        .upload.has-file { padding: 4px; border-color: var(--accent-2); }
        .upload-icon { font-size: 32px; color: var(--txt-2); margin-bottom: 8px; }
        .upload-text { color: var(--txt-2); font-size: 14px; }

        /* 🔥 PREVIEW */
        .preview { position: relative; width: 100%; max-height: 140px; border-radius: 6px; overflow: hidden; display: none; background: var(--bg-1); }
        .preview img { width: 100%; height: 100%; max-height: 140px; object-fit: contain; background: var(--bg-1); }
        .remove-btn { position: absolute; top: 8px; right: 8px; width: 28px; height: 28px; background: rgba(0, 0, 0, 0.8); border: none; border-radius: 50%; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s; font-size: 14px; }
        .remove-btn:hover { background: #ef4444; transform: scale(1.1); }
        .reupload-btn { position: absolute; bottom: 8px; left: 50%; transform: translateX(-50%); background: rgba(0, 0, 0, 0.8); color: white; border: none; border-radius: 6px; padding: 6px 12px; font-size: 12px; cursor: pointer; display: flex; align-items: center; gap: 4px; pointer-events: none; transition: opacity 0.3s; opacity: 0;}
        .preview:hover .reupload-btn {opacity: 1;pointer-events: auto;}

        /* 🔥 RESULTADOS */
        .results-header { padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 8px; }
        .results-content { flex: 1; overflow-y: auto; padding: 20px; }
        .result-group { background: var(--bg-2); border-radius: 12px; padding: 16px; margin-bottom: 20px; }
        .result-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid var(--border); }
        .result-info { font-size: 12px; color: var(--txt-2); }
        .status { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .status.completed { background: rgb(128, 236, 168); color: var(--accent-1); }
        .status.processing { background: rgba(251, 191, 36, 0.2); color: #fbbf24; }
        .status.failed { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
        .prompt-used { color: var(--txt-2); font-size: 11px; margin-top: 4px; font-style: italic; }

        /* 🔥 TOOLTIPS MEJORADOS PARA GUIDELINES */
        .tooltip {
            position: fixed;
            width: 1000px;
            height: auto;
            max-height: 70vh;
            background: var(--bg-1);
            border: 1px solid #444;
            border-radius: 12px;
            box-shadow: var(--shadow-heavy);
            z-index: 1050;
            display: none;
            overflow: hidden;
            color: rgb(0, 0, 0);
        }

        .tooltip-header {
            background: var(--bg-2);
            padding: 16px 20px;
            border-bottom: 1px solid #444;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .tooltip-header h6 {
            color: rgb(0, 0, 0);
            font-size: 16px;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }


        .tooltip-content p strong {
            color: rgb(0, 0, 0);
        }

        .card-img-top {
            height: 125px !important; /* Aumentamos la altura */
            object-fit: contain !important; /* Cambiamos a contain para mostrar imagen completa */
            background-color: #fff; /* Fondo blanco para mejor visualización */
            padding: 3px; /* Añadimos padding para que no toque los bordes */
        }



        /* 🔥 MODAL */
        .modal-content { background: var(--bg-2); border: 1px solid var(--border); border-radius: 12px; box-shadow: var(--shadow-heavy); }
        .modal-header { background: var(--bg-3); border-bottom: 1px solid var(--border); padding: 16px 20px; border-radius: 12px 12px 0 0; }
        .modal-title { color: var(--txt-1); font-size: 18px; font-weight: 600; margin: 0; }
        .close { color: var(--txt-2); font-size: 24px; opacity: 0.8; filter: invert(1); }
        .close:hover { color: var(--txt-1); opacity: 1; }

        .image-viewer-container {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 70vh;
            overflow: hidden;
            background: #1a1a1a;
            cursor: default;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }
        .image-viewer-container.dragging {cursor: grabbing !important;}

        #modalImage {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            cursor: default;
            transition: transform 0.1s ease-out;
            user-select: none;
            border-radius: 4px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        .zoom-indicator {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .zoom-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .zoom-controls button {
            min-width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #zoomLevel {
            min-width: 60px;
            text-align: center;
            font-weight: bold;
        }

        /* 🔍 AYUDA VISUAL */
        .zoom-help {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 11px;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
        }

        .image-viewer-container:hover .zoom-help {
            opacity: 1;
        }

        .image-viewer-container:hover .zoom-indicator {opacity: 1;}

        .vm-modal-image { width: 100%; height: 100%; object-fit: contain; cursor: grab; transition: transform 0.3s; }

        .image-controls { background: var(--bg-3); padding: 15px; border-top: 1px solid var(--border); border-radius: 0 0 12px 12px; }
        .zoom-controls { display: flex; align-items: center; }
        .image-overlay { position: absolute; top: 8px; right: 8px; background: rgba(0,0,0,0.7); border-radius: 4px; padding: 4px 8px; opacity: 0; transition: opacity 0.3s; }
        .image-overlay i { color: white; font-size: 12px; }

        /* 🔥 SCROLLBARS */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg-3); }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--txt-2); }

        /* 🔥 RESPONSIVE */
        @media (max-width: 768px) {
            .main-container { flex-direction: column; height: auto; }
            .left-panel { width: 100%; height: auto; }
            .grid-3, .grid-4 { grid-template-columns: repeat(2, 1fr); }
            .grid-6 { grid-template-columns: repeat(3, 1fr); gap: 10px; }
            .tooltip { width: 95vw; max-width: 800px; max-height: 80vh; }
            .tooltip-content { padding: 16px; }
            .guideline-image { height: 100px; }
            .guideline-label { height: 30px; font-size: 10px; }
        }

        @media (max-width: 576px) {
            .grid-6 { grid-template-columns: repeat(2, 1fr); gap: 8px; }
            .tooltip { width: 98vw; max-height: 70vh; }
            .guideline-image { height: 80px; }
            .guideline-label { height: 25px; font-size: 9px; }
        }

        /* 🔥 UTILIDADES */
        .d-none { display: none !important; }
        .file-input { position: absolute; opacity: 0; pointer-events: none; }
        .text-right { text-align: right; }

        /* 🍯 SWEETALERT2 CUSTOMIZACIÓN */
        .swal2-popup {
            font-family: 'Segoe UI', sans-serif !important;
            border-radius: 12px !important;
        }
        .swal2-title {
            color: var(--txt-1) !important;
        }
        .swal2-content {
            color: var(--txt-2) !important;
        }
        .swal2-confirm {
            background-color: var(--accent-2) !important;
            border: none !important;
        }
        .swal2-cancel {
            background-color: #dc3545 !important;
            border: none !important;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid px-4">
            <h3><i class="fas fa-magic"></i> SIO PLM - Probador Virtual con IA</h3>
            <div class="navbar-nav ml-auto">
                <a class="nav-link {{ request()->routeIs('virtual-model') ? 'active' : '' }}" href="{{ route('virtual-model') }}">
                    <i class="fas fa-user-plus"></i> Virtual Model
                </a>
                <a class="nav-link {{ request()->routeIs('virtual-try-on') ? 'active' : '' }}" href="{{ route('virtual-try-on') }}">
                    <i class="fas fa-tshirt"></i> AI Virtual Try-On
                </a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        @yield('content')
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    {{-- Eliminado bloque de SweetAlert2 por sesión, ya que todo se maneja por AJAX --}}
    @stack('scripts')
</body>
</html>
