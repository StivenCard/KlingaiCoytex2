<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'AI Virtual Try-On') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-1: #a0a0a0; --bg-2: #c5c5c5; --bg-3: #a0a0a0;
            --txt-1: #222222; --txt-2: #0c0c0c;
            --accent-1: #3b82f6; --accent-2: #3b82f6;
            --border: #b6b6b6;
        }

        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; background: var(--bg-1); color: var(--txt-1); }

        /* 🔥 NAVBAR */
        .navbar-custom { background: var(--bg-2) !important; border-bottom: 1px solid var(--border); padding: 12px 0; }
        .navbar-brand { color: var(--txt-1) !important; font-weight: 600; font-size: 1.25rem; }
        .navbar-brand:hover { color: var(--accent-2) !important; }
        .nav-link { color: var(--txt-2) !important; font-weight: 500; padding: 8px 16px !important; border-radius: 6px; transition: all 0.3s; }
        .nav-link:hover { color: var(--txt-1) !important; background: var(--bg-3); }
        .nav-link.active { color: var(--txt-1) !important; background: var(--accent-2); }

        /* 🔥 LAYOUT BASE */
        .main-content { padding: 0; margin: 0; width: 100%; height: calc(100vh - 70px); overflow: hidden; }
        .main-container { display: flex; height: 100%; overflow: hidden; }

        /* 🔥 PANELS */
        .left-panel { width: 450px; background: var(--bg-2); border-right: 1px solid var(--border); display: flex; flex-direction: column; overflow: hidden; }
        .right-panel { flex: 1; background: var(--bg-1); display: flex; flex-direction: column; overflow: hidden; }

        /* 🔥 TABS */
        .sub-tabs { display: flex; background: var(--bg-3); border-bottom: 1px solid var(--border); align-items: center; padding-right: 12px; }
        .sub-tab { flex: 1; padding: 12px; text-align: center; background: transparent; border: none; color: var(--txt-2); font-size: 13px; cursor: pointer; transition: all 0.3s; }
        .sub-tab.active { background: var(--bg-2); color: var(--txt-1); }

        /* 🔥 BOTONES INFO */
        .info-btn { width: 28px; height: 28px; background: transparent; border: 1px solid var(--border); color: var(--txt-2); border-radius: 50%; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; margin-left: 8px; font-size: 12px; }
        .info-btn:hover { background: var(--accent-2); color: white; border-color: var(--accent-2); }

        /* 🔥 CONTENEDORES */
        .scroll-container { flex: 1; overflow-y: auto; padding: 20px; }
        .section { border-bottom: 1px solid var(--border); padding: 20px; }
        .section:last-child { border-bottom: none; }
        .group { margin-bottom: 20px; }
        .group:last-child { margin-bottom: 0; }

        /* 🔥 LABELS Y TÍTULOS */
        .label { display: block; color: var(--txt-1); font-size: 13px; font-weight: 600; margin-bottom: 8px; }
        .title { color: var(--txt-1); margin-bottom: 16px; font-size: 14px; }

        /* 🔥 GRIDS */
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
        .grid-6 { display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px; margin-bottom: 0; }
        .grid-hints { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin-bottom: 16px; }

        /* 🔥 SELECTORES HORIZONTALES */
        .h-selector { display: flex; gap: 8px; }
        .selector-btn { flex: 1; padding: 8px 12px; background: var(--bg-3); border: 1px solid var(--border); color: var(--txt-2); border-radius: 6px; font-size: 12px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 4px; }
        .selector-btn:hover { border-color: var(--accent-2); color: var(--txt-1); }
        .selector-btn.active { background: var(--accent-2); border-color: var(--accent-2); color: white; }

        /* 🔥 BOTONES COLOR */
        .color-btn { width: 60px; height: 40px; border: 2px solid var(--border); border-radius: 8px; position: relative; flex: none; }
        .color-btn.active { border-color: var(--accent-2); box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3); }
        .color-check { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: rgba(0, 0, 0, 0.7); font-size: 14px; opacity: 0; transition: opacity 0.3s; }
        .color-btn.active .color-check { opacity: 1; }

        /* 🔥 ITEMS MODELO */
        .item { aspect-ratio: 3/4; background: var(--bg-3); border: 3px solid transparent; border-radius: 8px; overflow: hidden; cursor: pointer; transition: all 0.3s; position: relative; min-height: 140px; }
        .item:hover { border-color: var(--accent-2); box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3); }
        .item.selected { border-color: var(--accent-2); border-width: 4px; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5); }
        .item img { width: 100%; height: 100%; object-fit: cover; }

        /* 🔥 OVERLAYS */
        .overlay { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8)); padding: 8px; opacity: 0; transition: opacity 0.3s; }
        .item:hover .overlay { opacity: 1; }
        .badges { display: flex; gap: 4px; margin-bottom: 4px; flex-wrap: wrap; }
        .badge { font-size: 9px; padding: 2px 6px; border-radius: 3px; background: rgba(59, 130, 246, 0.8); color: white; font-weight: 500; }
        .badge.male { background: rgba(59, 130, 246, 0.8); }
        .badge.female { background: rgba(236, 72, 153, 0.8); }
        .overlay-title { color: white; font-size: 10px; font-weight: 600; margin-bottom: 2px; }
        .overlay-date { color: rgba(255, 255, 255, 0.7); font-size: 9px; }

        /* 🔥 TABS GARMENT */
        .tabs-container { display: flex; align-items: center; margin-bottom: 16px; }
        .tabs { flex: 1; display: flex; }
        .tab { flex: 1; padding: 8px 12px; background: transparent; border: 1px solid var(--border); color: var(--txt-2); font-size: 12px; cursor: pointer; transition: all 0.3s; }
        .tab:first-child { border-radius: 6px 0 0 6px; }
        .tab:last-child { border-radius: 0 6px 6px 0; border-left: none; }
        .tab.active { background: var(--accent-2); color: white; border-color: var(--accent-2); }

        /* 🔥 HINTS */
        .hint-btn { padding: 10px 12px; background: var(--bg-3); border: 1px solid var(--border); color: var(--txt-2); border-radius: 6px; font-size: 12px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 6px; }
        .hint-btn:hover { border-color: var(--accent-2); color: var(--txt-1); }
        .hint-btn.active { background: var(--accent-2); border-color: var(--accent-2); color: white; }

        /* 🔥 TEXTAREA */
        .prompt-container { background: var(--bg-3); border: 1px solid var(--border); border-radius: 8px; overflow: hidden; }
        .prompt-textarea { width: 100%; min-height: 100px; padding: 12px; background: transparent; border: none; color: var(--txt-1); font-size: 13px; resize: vertical; outline: none; font-family: inherit; }
        .prompt-textarea::placeholder { color: var(--txt-2); }
        .prompt-footer { display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: var(--bg-1); border-top: 1px solid var(--border); }
        .char-count { color: var(--txt-2); font-size: 11px; }
        .clear-btn { background: transparent; border: none; color: var(--txt-2); cursor: pointer; font-size: 11px; padding: 4px 8px; border-radius: 4px; transition: all 0.3s; }
        .clear-btn:hover { color: var(--txt-1); background: var(--bg-3); }

        /* 🔥 UPLOAD */
        .upload { border: 2px dashed var(--border); border-radius: 8px; padding: 30px; text-align: center; background: var(--bg-3); margin-bottom: 16px; cursor: pointer; transition: all 0.3s; position: relative; min-height: 120px; }
        .upload:hover { border-color: var(--accent-2); background: rgba(59, 130, 246, 0.1); }
        .upload.has-file { padding: 8px; border-color: var(--accent-2); }
        .upload-icon { font-size: 32px; color: var(--txt-2); margin-bottom: 8px; }
        .upload-text { color: var(--txt-2); font-size: 14px; }

        /* 🔥 PREVIEW */
        .preview { position: relative; width: 100%; max-height: 180px; border-radius: 6px; overflow: hidden; display: none; background: var(--bg-1); }
        .preview img { width: 100%; height: 100%; max-height: 180px; object-fit: contain; background: var(--bg-1); }
        .remove-btn { position: absolute; top: 8px; right: 8px; width: 28px; height: 28px; background: rgba(0, 0, 0, 0.8); border: none; border-radius: 50%; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s; font-size: 14px; }
        .remove-btn:hover { background: #ef4444; transform: scale(1.1); }
        .reupload-btn { position: absolute; bottom: 8px; left: 50%; transform: translateX(-50%); background: rgba(0, 0, 0, 0.8); color: white; border: none; border-radius: 6px; padding: 6px 12px; font-size: 12px; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: all 0.3s; }
        .reupload-btn:hover { background: var(--accent-2); }

        /* 🔥 SELECTORS */
        .selector { background: var(--bg-3); border: 1px solid var(--border); color: var(--txt-1); border-radius: 6px; padding: 8px 12px; width: 100%; margin-bottom: 16px; }

        /* 🔥 BOTÓN GENERAR */
        .btn-generate { width: 100%; padding: 12px; background: var(--accent-2); border: none; border-radius: 8px; color: white; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.3s; }
        .btn-generate:hover { background: #2c2fe7;}
        .btn-generate:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        /* 🔥 RESULTADOS */
        .results-header { padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 8px; }
        .results-content { flex: 1; overflow-y: auto; padding: 20px; }
        .result-group { background: var(--bg-2); border-radius: 12px; padding: 16px; margin-bottom: 20px; }
        .result-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid var(--border); }
        .result-info { font-size: 12px; color: var(--txt-2); }
        .status { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .status.completed { background: rgba(74, 222, 128, 0.2); color: var(--accent-1); }
        .status.processing { background: rgba(251, 191, 36, 0.2); color: #fbbf24; }
        .status.failed { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
        .prompt-used { color: var(--txt-2); font-size: 11px; margin-top: 4px; font-style: italic; }

        /* 🔥 ALERTS */
        .alert { border: none; border-radius: 8px; border-left: 4px solid; }
        .alert-success { background: rgba(74, 222, 128, 0.1); color: var(--accent-1); border-left-color: var(--accent-1); }
        .alert-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-left-color: #ef4444; }
        .alert-warning { background: rgba(251, 191, 36, 0.1); color: #fbbf24; border-left-color: #fbbf24; }

        /* 🔥 TOOLTIPS */
        .tooltip { position: fixed; width: 1000px; height: auto; max-height: 600px; background: var(--bg-2); border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5); z-index: 1050; display: none; overflow: hidden; }
        .tooltip-header { background: var(--bg-3); padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .tooltip-header h6 { color: var(--txt-1); font-size: 16px; font-weight: 600; margin: 0; display: flex; align-items: center; gap: 8px; }
        .tooltip-close { background: none; border: none; color: var(--txt-2); cursor: pointer; font-size: 14px; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .tooltip-close:hover { background: var(--bg-1); color: var(--txt-1); }
        .tooltip-content { padding: 20px; overflow: hidden; }
        .tooltip-specs { background: var(--bg-3); padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; text-align: center; }
        .tooltip-specs p { color: var(--txt-1); font-size: 13px; font-weight: 600; margin: 0; }
        .tooltip-specs ul { margin: 8px 0 0 0; padding: 0; list-style: none; display: flex; justify-content: center; gap: 30px; color: var(--txt-2); font-size: 12px; }
        .guidelines-section { margin-bottom: 20px; }
        .guidelines-section:last-child { margin-bottom: 0; }
        .section-title { color: var(--txt-1); font-size: 14px; font-weight: 600; margin-bottom: 12px; text-align: center; padding: 8px 0; border-radius: 6px; }
        .section-title.valid { color: var(--accent-1); background: rgba(74, 222, 128, 0.1); }
        .section-title.invalid { color: #ef4444; background: rgba(239, 68, 68, 0.1); }
        .guideline-item { display: flex; flex-direction: column; align-items: center; text-align: center; }
        .guideline-image { aspect-ratio: 3/4; width: 100%; height: 140px; background: var(--bg-3); border-radius: 8px; overflow: hidden; border: 1px solid var(--border); margin-bottom: 8px; }
        .guideline-image img { width: 100%; height: 100%; object-fit: cover; }
        .guideline-label { color: var(--txt-2); font-size: 11px; line-height: 1.3; max-width: 100%; word-wrap: break-word; height: 40px; overflow: hidden; display: flex; align-items: flex-start; gap: 4px; }
        .guideline-label.valid::before { content: "✓"; color: var(--accent-1); font-weight: bold; font-size: 12px; flex-shrink: 0; }
        .guideline-label.invalid::before { content: "✗"; color: #ef4444; font-weight: bold; font-size: 12px; flex-shrink: 0; }
        .no-images { grid-column: 1 / -1; text-align: center; color: var(--txt-2); font-size: 12px; padding: 40px 20px; }

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
            .grid-hints { grid-template-columns: 1fr; }
            .h-selector { flex-wrap: wrap; }
            .selector-btn { min-width: 80px; }
            .tooltip { width: 95vw; max-width: 800px; max-height: 80vh; }
            .tooltip-content { padding: 16px; }
            .tooltip-specs ul { flex-direction: column; gap: 6px; }
            .guideline-image { height: 120px; }
            .guideline-label { height: 35px; font-size: 10px; }
        }

        @media (max-width: 576px) {
            .grid-6 { grid-template-columns: repeat(2, 1fr); gap: 8px; }
            .tooltip { width: 98vw; max-height: 70vh; }
            .guideline-image { height: 100px; }
            .guideline-label { height: 30px; font-size: 9px; }
        }

        /* 🔥 UTILIDADES */
        .d-none { display: none !important; }
        .file-input { position: absolute; opacity: 0; pointer-events: none; }
        .btn-close { filter: invert(1); }
    </style>
</head>
<body>
    <!-- 🔥 NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid px-4">
                <h3><i class="fas fa-magic"></i> SIO PLM - Probador Virtual con IA</h3>
            <div class="navbar-nav ms-auto">
                <a class="nav-link {{ request()->routeIs('virtual-model') ? 'active' : '' }}" href="{{ route('virtual-model') }}">
                    <i class="fas fa-user-plus"></i> Virtual Model
                </a>
                <a class="nav-link {{ request()->routeIs('virtual-try-on') ? 'active' : '' }}" href="{{ route('virtual-try-on') }}">
                    <i class="fas fa-tshirt"></i> AI Virtual Try-On
                </a>
            </div>
        </div>
    </nav>

    <!-- 🔥 ALERTAS -->
    @if(session('success'))
        <div class="container-fluid px-4 mt-3">
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="container-fluid px-4 mt-3">
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="container-fluid px-4 mt-3">
            <div class="alert alert-warning alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Errores de validación:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    <!-- 🔥 CONTENIDO PRINCIPAL -->
    <div class="main-content">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @stack('scripts')
</body>
</html>
