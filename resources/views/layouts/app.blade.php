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
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --bg-tertiary: #3a3a3a;
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
            --accent-green: #4ade80;
            --accent-blue: #3b82f6;
            --border-color: #404040;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* 🔥 NAVBAR */
        .navbar-custom {
            background-color: var(--bg-secondary) !important;
            border-bottom: 1px solid var(--border-color);
            padding: 12px 0;
        }

        .navbar-brand {
            color: var(--text-primary) !important;
            font-weight: 600;
            font-size: 1.25rem;
        }

        .navbar-brand:hover {
            color: var(--accent-blue) !important;
        }

        .nav-link {
            color: var(--text-secondary) !important;
            font-weight: 500;
            padding: 8px 16px !important;
            border-radius: 6px;
            transition: all 0.3s;
        }

        .nav-link:hover {
            color: var(--text-primary) !important;
            background-color: var(--bg-tertiary);
        }

        .nav-link.active {
            color: var(--text-primary) !important;
            background-color: var(--accent-blue);
        }

        /* 🔥 CONTAINER PRINCIPAL */
        .main-content {
            padding: 0;
            margin: 0;
            width: 100%;
            height: calc(100vh - 70px);
            overflow: hidden;
        }

        .main-container {
            display: flex;
            height: 100%;
            overflow: hidden;
        }

        /* 🔥 LEFT PANEL - VIRTUAL TRY-ON */
        .left-panel {
            width: 450px;
            background-color: var(--bg-secondary);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sub-tabs {
            display: flex;
            background-color: var(--bg-tertiary);
            border-bottom: 1px solid var(--border-color);
            align-items: center;
            padding-right: 12px;
        }

        .sub-tab {
            flex: 1;
            padding: 12px;
            text-align: center;
            background: transparent;
            border: none;
            color: var(--text-secondary);
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .sub-tab.active {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
        }

        .info-btn {
            width: 28px;
            height: 28px;
            background-color: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 8px;
            font-size: 12px;
        }

        .info-btn:hover {
            background-color: var(--accent-blue);
            color: white;
            border-color: var(--accent-blue);
        }

        .model-grid-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .model-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .model-item {
            aspect-ratio: 3/4;
            background-color: var(--bg-tertiary);
            border: 3px solid transparent;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            min-height: 140px;
        }

        .model-item:hover {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
        }

        .model-item.selected {
            border-color: var(--accent-blue);
            border-width: 4px;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
        }

        .model-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* 🔥 GARMENT SECTION */
        .garment-section {
            border-top: 1px solid var(--border-color);
            padding: 20px;
        }

        .garment-tabs-container {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
        }

        .garment-tabs {
            flex: 1;
            display: flex;
        }

        .garment-tab {
            flex: 1;
            padding: 8px 12px;
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .garment-tab:first-child {
            border-radius: 6px 0 0 6px;
        }

        .garment-tab:last-child {
            border-radius: 0 6px 6px 0;
            border-left: none;
        }

        .garment-tab.active {
            background-color: var(--accent-blue);
            color: white;
            border-color: var(--accent-blue);
        }

        /* 🔥 UPLOAD AREAS */
        .upload-area {
            border: 2px dashed var(--border-color);
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            background-color: var(--bg-tertiary);
            margin-bottom: 16px;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            min-height: 120px;
        }

        .upload-area:hover {
            border-color: var(--accent-blue);
            background-color: rgba(59, 130, 246, 0.1);
        }

        .upload-area.has-file {
            padding: 8px;
            border-color: var(--accent-blue);
        }

        .upload-icon {
            font-size: 32px;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }

        .upload-text {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .image-preview {
            position: relative;
            width: 100%;
            max-height: 180px;
            border-radius: 6px;
            overflow: hidden;
            display: none;
            background-color: var(--bg-primary);
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            max-height: 180px;
            object-fit: contain;
            background-color: var(--bg-primary);
        }

        .image-preview .remove-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 28px;
            height: 28px;
            background: rgba(0, 0, 0, 0.8);
            border: none;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            font-size: 14px;
        }

        .image-preview .remove-btn:hover {
            background: #ef4444;
            transform: scale(1.1);
        }

        .image-preview .reupload-btn {
            position: absolute;
            bottom: 8px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: all 0.3s;
        }

        .image-preview .reupload-btn:hover {
            background: var(--accent-blue);
        }

        /* 🔥 SELECTORS Y BOTONES */
        .output-selector {
            background-color: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 6px;
            padding: 8px 12px;
            width: 100%;
            margin-bottom: 16px;
        }

        .generate-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--accent-green), #22c55e);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .generate-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(74, 222, 128, 0.3);
        }

        .generate-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* 🔥 RIGHT PANEL - RESULTADOS */
        .right-panel {
            flex: 1;
            background-color: var(--bg-primary);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .results-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .results-content {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .result-group {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .result-group-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border-color);
        }

        .result-group-info {
            font-size: 12px;
            color: var(--text-secondary);
        }

        .result-group-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-completed {
            background-color: rgba(74, 222, 128, 0.2);
            color: var(--accent-green);
        }

        .status-processing {
            background-color: rgba(251, 191, 36, 0.2);
            color: #fbbf24;
        }

        .status-failed {
            background-color: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .result-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }

        .result-item {
            aspect-ratio: 3/4;
            background-color: var(--bg-tertiary);
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }

        .result-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* 🔥 ALERTAS */
        .alert {
            border: none;
            border-radius: 8px;
            border-left: 4px solid;
        }

        .alert-success {
            background-color: rgba(74, 222, 128, 0.1);
            color: var(--accent-green);
            border-left-color: var(--accent-green);
        }

        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-left-color: #ef4444;
        }

        .alert-warning {
            background-color: rgba(251, 191, 36, 0.1);
            color: #fbbf24;
            border-left-color: #fbbf24;
        }

        /* 🔥 GUIDELINES TOOLTIPS */
        .info-tooltip {
            position: fixed;
            width: 1000px;
            height: auto;
            max-height: 600px;
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
            z-index: 1050;
            display: none;
            overflow: hidden;
        }

        .tooltip-header {
            background-color: var(--bg-tertiary);
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .tooltip-header h6 {
            color: var(--text-primary);
            font-size: 16px;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tooltip-close {
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 14px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .tooltip-close:hover {
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }

        .tooltip-content {
            padding: 20px;
            overflow: hidden;
        }

        .tooltip-specs {
            background-color: var(--bg-tertiary);
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }

        .tooltip-specs p {
            color: var(--text-primary);
            font-size: 13px;
            font-weight: 600;
            margin: 0;
        }

        .tooltip-specs ul {
            margin: 8px 0 0 0;
            padding: 0;
            list-style: none;
            display: flex;
            justify-content: center;
            gap: 30px;
            color: var(--text-secondary);
            font-size: 12px;
        }

        .guidelines-section {
            margin-bottom: 20px;
        }

        .guidelines-section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            color: var(--text-primary);
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 12px;
            text-align: center;
            padding: 8px 0;
            border-radius: 6px;
        }

        .section-title.valid {
            color: var(--accent-green);
            background-color: rgba(74, 222, 128, 0.1);
        }

        .section-title.invalid {
            color: #ef4444;
            background-color: rgba(239, 68, 68, 0.1);
        }

        .guidelines-images-single-row {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 12px;
            margin-bottom: 0;
        }

        .guideline-item-horizontal {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .guideline-image {
            aspect-ratio: 3/4;
            width: 100%;
            height: 140px;
            background-color: var(--bg-tertiary);
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border-color);
            margin-bottom: 8px;
        }

        .guideline-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .guideline-label {
            color: var(--text-secondary);
            font-size: 11px;
            line-height: 1.3;
            max-width: 100%;
            word-wrap: break-word;
            height: 40px;
            overflow: hidden;
            display: flex;
            align-items: flex-start;
            gap: 4px;
        }

        .guideline-label.valid::before {
            content: "✓";
            color: var(--accent-green);
            font-weight: bold;
            font-size: 12px;
            flex-shrink: 0;
        }

        .guideline-label.invalid::before {
            content: "✗";
            color: #ef4444;
            font-weight: bold;
            font-size: 12px;
            flex-shrink: 0;
        }

        .no-images {
            grid-column: 1 / -1;
            text-align: center;
            color: var(--text-secondary);
            font-size: 12px;
            padding: 40px 20px;
        }

        /* 🔥 SCROLLBARS */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-tertiary);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--text-secondary);
        }

        /* 🔥 RESPONSIVE */
        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
                height: auto;
            }

            .left-panel {
                width: 100%;
                height: auto;
            }

            .model-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .result-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .info-tooltip {
                width: 95vw;
                max-width: 800px;
                max-height: 80vh;
            }

            .guidelines-images-single-row {
                grid-template-columns: repeat(3, 1fr);
                gap: 10px;
            }

            .tooltip-content {
                padding: 16px;
            }

            .tooltip-specs ul {
                flex-direction: column;
                gap: 6px;
            }

            .guideline-image {
                height: 120px;
            }

            .guideline-label {
                height: 35px;
                font-size: 10px;
            }
        }

        @media (max-width: 576px) {
            .guidelines-images-single-row {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }

            .info-tooltip {
                width: 98vw;
                max-height: 70vh;
            }

            .guideline-image {
                height: 100px;
            }

            .guideline-label {
                height: 30px;
                font-size: 9px;
            }
        }

        /* 🔥 UTILIDADES */
        .d-none {
            display: none !important;
        }

        .file-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .btn-close {
            filter: invert(1);
        }
    </style>
</head>
<body>
    <!-- 🔥 NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="/">
                <i class="fas fa-magic"></i> Virtual Try-On Coytex
            </a>
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
