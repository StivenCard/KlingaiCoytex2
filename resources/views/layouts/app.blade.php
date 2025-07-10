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
            overflow-x: hidden;
        }

        /* HEADER STYLES */
        .main-header {
            background-color: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            padding: 0;
        }

        .main-tabs {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            gap: 40px;
        }

        .main-tab {
            color: var(--text-secondary);
            text-decoration: none;
            padding: 8px 0;
            border-bottom: 2px solid transparent;
            font-weight: 500;
            transition: all 0.3s;
        }

        .main-tab.active {
            color: var(--text-primary);
            border-bottom-color: var(--accent-blue);
        }

        .help-icons {
            margin-left: auto;
            display: flex;
            gap: 20px;
        }

        .help-icon {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* MAIN CONTAINER */
        .main-container {
            display: flex;
            height: calc(100vh - 60px);
            overflow: hidden;
        }

        /* LEFT PANEL */
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

        /* MODEL GRID - 🔥 CAMBIO 1: GRID 3 COLUMNAS */
        .model-grid-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .model-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* 🔥 CAMBIADO: de 2 a 3 columnas */
            gap: 12px; /* 🔥 AJUSTADO: gap más pequeño para 3 columnas */
            margin-bottom: 20px;
        }

        .model-item {
            aspect-ratio: 3/4;
            background-color: var(--bg-tertiary);
            border: 3px solid transparent; /* 🔥 CAMBIO 2: borde más grueso */
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            min-height: 140px; /* 🔥 AJUSTADO: altura para 3 columnas */
        }

        .model-item:hover {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3); /* 🔥 MEJORADO: shadow más visible */
        }

        .model-item.selected {
            border-color: var(--accent-blue);
            border-width: 4px; /* 🔥 CAMBIO 2: borde aún más grueso cuando está seleccionado */
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5); /* 🔥 MEJORADO: shadow más fuerte */
        }

        .model-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* GARMENT SECTION */
        .garment-section {
            border-top: 1px solid var(--border-color);
            padding: 20px;
        }

        .garment-tabs {
            display: flex;
            margin-bottom: 16px;
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

        /* UPLOAD AREA - 🔥 CAMBIO 3: PREVIEW CORREGIDO */
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
            padding: 8px; /* 🔥 CORREGIDO: padding reducido para preview */
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

        /* 🔥 CAMBIO 3: PREVIEW CORREGIDO - NO CORTADO */
        .image-preview {
            position: relative;
            width: 100%;
            max-height: 180px; /* 🔥 CORREGIDO: altura máxima controlada */
            border-radius: 6px;
            overflow: hidden;
            display: none;
            background-color: var(--bg-primary);
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            max-height: 180px; /* 🔥 CORREGIDO: altura máxima para la imagen */
            object-fit: contain; /* 🔥 CORREGIDO: contain en lugar de cover para mostrar imagen completa */
            background-color: var(--bg-primary);
        }

        .image-preview .remove-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 28px; /* 🔥 MEJORADO: botón más grande */
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

        /* CONTROLS */
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

        /* RIGHT PANEL */
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

        .result-section {
            margin-bottom: 30px;
        }

        .result-title {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
            font-size: 16px;
            font-weight: 600;
        }

        /* RESULTADO INDIVIDUAL CON METADATA */
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

        /* SCROLLBARS */
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

        /* RESPONSIVE */
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
                grid-template-columns: repeat(2, 1fr); /* En móvil mantener 2 columnas */
            }

            .result-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* HIDDEN ELEMENTS */
        .d-none {
            display: none !important;
        }

        /* FILE INPUT STYLING */
        .file-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
    </style>
</head>
<body>
    @yield('content')

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @stack('scripts')
</body>
</html>
