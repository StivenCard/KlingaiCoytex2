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
            --bg-1: #FFFFF0;
            --bg-2: #ededed;
            --bg-3: #ededed;
            --txt-1: #222222; --txt-2: #0c0c0c;
            --accent-1: #3b82f6; --accent-2: #3b82f6;
            --border: rgb(177, 177, 177);
            --shadow-light: 0 0 0 2px rgba(59, 130, 246, 0.3);
            --shadow-medium: 0 0 0 3px rgba(59, 130, 246, 0.5);
            --shadow-heavy: 0 8px 32px rgba(0, 0, 0, 0.5);
        }

        /* * { box-sizing: border-box; }
        body { margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; background: var(--bg-1); color: var(--txt-1); } */

        /* 🔥 NAVBAR */
        .probador-virtual-container .navbar-custom { background: #E7EBDC !important; border-bottom: 1px solid var(--border); padding: 12px 0; }
        .probador-virtual-container .navbar-brand { color: var(--txt-1) !important; font-weight: 600; font-size: 1.25rem; }
        .probador-virtual-container .navbar-brand:hover { color: var(--accent-2) !important; }
        .probador-virtual-container .nav-link { color: var(--txt-2) !important; font-weight: 500; padding: 8px 16px !important; border-radius: 6px; transition: all 0.3s; }
        .probador-virtual-container .nav-link:hover { color: #D3D3D3 !important; background: #545b62; }
        .probador-virtual-container .nav-link.active { color: var(--txt-1) !important; background: var(--accent-2); }

        /* 🔥 LAYOUT */
        .probador-virtual-container .main-content { padding: 0; margin: 0; width: 100%;  overflow: hidden; }
        .probador-virtual-container .main-container { display: flex; height: 100%; overflow: hidden; }
        .probador-virtual-container .left-panel { width: 520px; background: var(--bg-2); border-right: 1px solid var(--border); display: flex; flex-direction: column; overflow: hidden; }
        .probador-virtual-container .right-panel { flex: 1; background: var(--bg-1); display: flex; flex-direction: column; overflow: hidden; }

        /* 🔥 TABS RESTAURADOS */
        .probador-virtual-container .sub-tabs { display: flex; background: var(--bg-3); border-bottom: 1px solid var(--border); align-items: center; padding-right: 12px; }
        .probador-virtual-container .sub-tab {
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
        .probador-virtual-container .sub-tab.active { background: var(--bg-2); color: var(--txt-1); }
        .probador-virtual-container .probador-virtual-container .sub-tab:hover { background: var(--bg-1); color: var(--txt-1); }

        /* 🔥 TABS GARMENT RESTAURADOS */
        .probador-virtual-container .tabs-container { display: flex; align-items: center; margin-bottom: 16px; }
        .probador-virtual-container .tabs { flex: 1; display: flex; }
        .probador-virtual-container  .tab {
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
        .probador-virtual-container  .tab:first-child { border-radius: 6px 0 0 6px; }
        .probador-virtual-container  .tab:last-child { border-radius: 0 6px 6px 0; border-left: none; }
        .probador-virtual-container  .tab.active { background: var(--accent-2); color: white; border-color: var(--accent-2); }
        .probador-virtual-container  .tab:hover { background: var(--bg-1); color: var(--txt-1); }

        /* 🔥 BOTONES INFO RESTAURADOS */
        .probador-virtual-container  .info-btn {
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
        .probador-virtual-container  .info-btn:hover { background: var(--accent-2); color: white; border-color: var(--accent-2); }

        /* 🔥 CONTENEDORES */
        .probador-virtual-container  .scroll-container {overflow-y: scroll; scrollbar-width: none; -ms-overflow-style: none; padding: 10px; max-height: 23rem}
        .probador-virtual-container  .scroll-container::-webkit-scrollbar {display: none;}
        .probador-virtual-container  .section { margin: 2.5rem 0 0 0;border: 0 1px 0 0 solid var(--border);}
        .probador-virtual-container  .section:last-child { border-bottom: none; }
        .probador-virtual-container  .group { margin-bottom: 20px; }
        .probador-virtual-container  .group:last-child { margin-bottom: 0; }

        /* 🔥 LABELS */
        .probador-virtual-container  .label { display: block; color: var(--txt-1); font-size: 13px; font-weight: 600; margin-bottom: 8px; }
        .probador-virtual-container  .title { color: var(--txt-1); margin-bottom: 16px; font-size: 14px; }

        /* 🔥 GRIDS */
        .probador-virtual-container  .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px; }
        .probador-virtual-container  .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
        .probador-virtual-container  .grid-6 { display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px; margin-bottom: 0; }
        .probador-virtual-container  .no-images {grid-column: 1 / -1;/* Esto hace que ocupe todas las columnas del grid */ min-height: 200px; }

        /* 🔥 BOTONES COLOR */
        .probador-virtual-container  .color-btn { width: 60px; height: 40px; border: 2px solid var(--border); border-radius: 8px; position: relative; flex: none; }
        .probador-virtual-container  .color-btn.active { border-color: var(--accent-2); box-shadow: var(--shadow-light); }
        .probador-virtual-container  .color-check { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: rgba(0, 0, 0, 0.7); font-size: 14px; opacity: 0; transition: opacity 0.3s; }
        .probador-virtual-container  .color-btn.active .color-check { opacity: 1; }

        /* 🔥 ITEMS MODELO */
        .probador-virtual-container  .item { aspect-ratio: 3/4; background: var(--bg-3); border: 3px solid transparent; border-radius: 8px; overflow: hidden; cursor: pointer; transition: all 0.3s; position: relative; min-height: 140px; }
        .probador-virtual-container  .item:hover { border-color: var(--accent-2); box-shadow: var(--shadow-light); }
        .probador-virtual-container  .item.selected { border-color: var(--accent-2); border-width: 4px; box-shadow: var(--shadow-medium); }
        .probador-virtual-container  .item img { width: 100%; height: 100%; object-fit: cover; }

        /* 🔥 OVERLAYS */
        .probador-virtual-container  .overlay { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8)); padding: 8px; opacity: 0; transition: opacity 0.3s; }
        .probador-virtual-container  .item:hover .overlay { opacity: 1; }
        .probador-virtual-container  .badges { display: flex; gap: 4px; margin-bottom: 4px; flex-wrap: wrap; }
        .probador-virtual-container  .badge.male { background: rgba(59, 130, 246, 0.8); }
        .probador-virtual-container  .badge.female { background: rgba(236, 72, 153, 0.8); }
        .probador-virtual-container  .overlay-title { color: white; font-size: 10px; font-weight: 600; margin-bottom: 2px; }
        .probador-virtual-container  .overlay-date { color: rgba(255, 255, 255, 0.7); font-size: 9px; }

        /* 🔥 UPLOAD */
        .probador-virtual-container  .upload { border: 2px dashed var(--border); border-radius: 8px; padding:15px; text-align: center; background: var(--bg-3); margin-bottom: 16px; cursor: pointer; transition: all 0.3s; position: relative; min-height: 80px; }
        .probador-virtual-container  .upload:hover { border-color: var(--accent-2); background: rgba(59, 130, 246, 0.1); }
        .probador-virtual-container  .upload.has-file { padding: 4px; border-color: var(--accent-2); }
        .probador-virtual-container  .upload-icon { font-size: 32px; color: var(--txt-2); margin-bottom: 8px; }
        .probador-virtual-container  .upload-text { color: var(--txt-2); font-size: 14px; }

        /* 🔥 PREVIEW */
        .probador-virtual-container  .preview { position: relative; width: 100%; max-height: 140px; border-radius: 6px; overflow: hidden; display: none; background: var(--bg-1); }
        .probador-virtual-container  .preview img { width: 100%; height: 100%; max-height: 140px; object-fit: contain; background: var(--bg-1); }
        .probador-virtual-container  .remove-btn { position: absolute; top: 8px; right: 8px; width: 28px; height: 28px; background: rgba(0, 0, 0, 0.8); border: none; border-radius: 50%; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s; font-size: 14px; }
        .probador-virtual-container  .remove-btn:hover { background: #ef4444; transform: scale(1.1); }
        .probador-virtual-container  .reupload-btn { position: absolute; bottom: 8px; left: 50%; transform: translateX(-50%); background: rgba(0, 0, 0, 0.8); color: white; border: none; border-radius: 6px; padding: 6px 12px; font-size: 12px; cursor: pointer; display: flex; align-items: center; gap: 4px; pointer-events: none; transition: opacity 0.3s; opacity: 0;}
        .probador-virtual-container  .preview:hover .reupload-btn {opacity: 1;pointer-events: auto;}

        /* 🔥 RESULTADOS */
        .probador-virtual-container  .results-header { padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 8px; }
        .probador-virtual-container  .results-content { flex: 1; overflow-y: auto; padding: 0 20px;}
        .probador-virtual-container  .result-group { background: var(--bg-2); border-radius: 12px; padding: 16px; margin-bottom: 20px; border: 1px solid var(--border);}
        .probador-virtual-container  .result-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid var(--border); }
        .probador-virtual-container  .result-info { font-size: 12px; color: var(--txt-2); }
        .probador-virtual-container  .status { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .probador-virtual-container  .status.completed { background: rgb(6, 192, 0); color: #ffffff; }
        .probador-virtual-container  .status.processing { background: orange; color: #fffff; }
        .probador-virtual-container  .status.failed { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
        .probador-virtual-container  .prompt-used { color: var(--txt-2); font-size: 11px; margin-top: 4px; font-style: italic; }

        /* 🔥 TOOLTIPS MEJORADOS PARA GUIDELINES */
        .probador-virtual-container .tooltip {
            position: fixed;
            max-width: 80%;
            max-height: 90%;
            background: var(--bg-1);
            border: 1px solid #444;
            border-radius: 12px;
            box-shadow: var(--shadow-heavy);
            z-index: 1050;
            display: none;
            overflow: hidden;
            color: rgb(0, 0, 0);
        }

        .probador-virtual-container .tooltip-header {
            background: var(--bg-2);
            padding: 16px 20px;
            border-bottom: 1px solid #444;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .probador-virtual-container .tooltip-header h6 {
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

        .probador-virtual-container  .tooltip-content {
            padding: 20px;
            overflow-y: auto;
            max-height: 45rem;
        }

        .probador-virtual-container  .tooltip-content p {
            color: #ff0000;
            font-size: 14px;
            line-height: 1.5;
        }

        .probador-virtual-container  .tooltip-content p strong {
            color: rgb(0, 0, 0);
        }

        /* 🔥 MODAL */
        .probador-virtual-container  .modal-content { background: var(--bg-2); border: 1px solid var(--border); border-radius: 12px; box-shadow: var(--shadow-heavy); }
        .probador-virtual-container  .modal-header { background: var(--bg-3); border-bottom: 1px solid var(--border); padding: 16px 20px; border-radius: 12px 12px 0 0; }
        .probador-virtual-container  .modal-title { color: var(--txt-1); font-size: 18px; font-weight: 600; margin: 0; }
        .probador-virtual-container  .close { color: var(--txt-2); font-size: 24px; opacity: 0.8; filter: invert(1); }
        .probador-virtual-container  .close:hover { color: var(--txt-1); opacity: 1; }

        .probador-virtual-container  .image-viewer-container {
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
        .probador-virtual-container  .image-viewer-container.dragging {cursor: grabbing !important;}

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

        .probador-virtual-container  .zoom-indicator {
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

        .probador-virtual-container  .zoom-controls {
            display: flex;
            align-items: center;
        }

        .probador-virtual-container  .zoom-controls button {
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
        .probador-virtual-container  .zoom-help {
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

        .probador-virtual-container  .image-viewer-container:hover .zoom-help {
            opacity: 1;
        }

        .probador-virtual-container  .image-viewer-container:hover .zoom-indicator {opacity: 1;}

        .probador-virtual-container  .vm-modal-image { width: 100%; height: 100%; object-fit: contain; cursor: grab; transition: transform 0.3s; }

        .probador-virtual-container  .image-controls { background: var(--bg-3); padding: 15px; border-top: 1px solid var(--border); border-radius: 0 0 12px 12px; }
        .probador-virtual-container  .zoom-controls { display: flex; align-items: center; }
        .probador-virtual-container  .image-overlay { position: absolute; top: 8px; right: 8px; background: rgba(0,0,0,0.7); border-radius: 4px; padding: 4px 8px; opacity: 0; transition: opacity 0.3s; }
        .probador-virtual-container  .image-overlay i { color: white; font-size: 12px; }

        /* 🔥 SCROLLBARS */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg-3); }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--txt-2); }

        /* 🔥 RESPONSIVE */
        @media (max-width: 768px) {
            .probador-virtual-container  .main-container { flex-direction: column; height: auto; }
            .probador-virtual-container  .left-panel { width: 100%; height: auto; }
            .probador-virtual-container  .grid-3, .grid-4 { grid-template-columns: repeat(2, 1fr); }
            .probador-virtual-container  .grid-6 { grid-template-columns: repeat(3, 1fr); gap: 10px; }
            .probador-virtual-container  .tooltip { width: 95vw; max-width: 800px; max-height: 80vh; }
            .probador-virtual-container  .tooltip-content { padding: 16px; }
            .probador-virtual-container  .guideline-image { height: 100px; }
            .probador-virtual-container  .guideline-label { height: 30px; font-size: 10px; }
        }

        @media (max-width: 576px) {
            .probador-virtual-container  .grid-6 { grid-template-columns: repeat(2, 1fr); gap: 8px; }
            .probador-virtual-container  .tooltip { width: 98vw; max-height: 70vh; }
            .probador-virtual-container  .guideline-image { height: 80px; }
            .probador-virtual-container  .guideline-label { height: 25px; font-size: 9px; }
        }

        /* 🔥 UTILIDADES */
        .probador-virtual-container  .d-none { display: none !important; }
        .probador-virtual-container  .file-input { position: absolute; opacity: 0; pointer-events: none; }
        .probador-virtual-container  .text-right { text-align: right; }

        /* 🍯 SWEETALERT2 CUSTOMIZACIÓN */
        .probador-virtual-container  .swal2-popup {
            font-family: 'Segoe UI', sans-serif !important;
            border-radius: 12px !important;
        }
        .probador-virtual-container  .swal2-title {
            color: var(--txt-1) !important;
        }
        .probador-virtual-container  .swal2-content {
            color: var(--txt-2) !important;
        }
        .probador-virtual-container  .swal2-confirm {
            background-color: var(--accent-2) !important;
            border: none !important;
        }
        .probador-virtual-container  .swal2-cancel {
            background-color: #dc3545 !important;
            border: none !important;
        }

        .probador-virtual-container .goBackUp {
            display: none; /* Hidden by default */
            position: fixed; /* Fixed/sticky position */
            bottom: 20px; /* Place the button at the bottom of the page */
            right: 20px; /* Place the button 30px from the right */
            z-index: 99; /* Make sure it does not overlap */
            border: none; /* Remove borders */
            outline: none; /* Remove outline */
            background-color: orange; /* Set a background color */
            color: white; /* Text color */
            cursor: pointer; /* Add a mouse pointer on hover */
            width: 3rem;
            height: 3rem;
            border-radius: 50%; /* Rounded corners */
            font-size: 18px; /* Increase font size */
            text-align: center;
        }

            .probador-virtual-container .goBackUp:hover {
            background-color: #ffffff; /* Add a dark-grey background on hover */
            color: orange;
            border: 0.2rem solid orange;
            width: 2.9rem;
            height: 2.9rem;
            }

            /* Estilos específicos para video */
        .probador-virtual-container .video-container {
            position: relative;
            width: 100%;
            background: #000;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 400px;
        }

        .probador-virtual-container #modalVideo {
            max-width: 100%;
            max-height: 70vh;
        }

        .probador-virtual-container .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 1rem;
            padding: 1rem;
        }

        .probador-virtual-container .video-item {
            position: relative;
            aspect-ratio: 16/9;
            cursor: pointer;
            border-radius: 8px;
            overflow: hidden;
            background: #000;
        }

        .probador-virtual-container .preview-video {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .probador-virtual-container .video-overlay {
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

        .probador-virtual-container .video-overlay i {
            color: white;
            font-size: 2rem;
        }

        .probador-virtual-container .video-item:hover .video-overlay {
            opacity: 1;
        }

        .probador-virtual-container .video-controls {
            padding: 1rem;
            background: var(--bg-3);
            border-top: 1px solid var(--border);
        }

        .probador-virtual-container .upload.upload-dropdown {
            padding: 0 !important;
        }

        .probador-virtual-container .btn-upload {
            background: none;
            /* border: 2px dashed var(--border); */
            border: none;
            /* border-radius: 8px; */
            padding: 15px;
            text-align: center;
            transition: all 0.3s;
        }

        .probador-virtual-container .btn-upload:hover, .btn-upload:focus {
            border-color: var(--accent-2);
            background: rgba(59, 130, 246, 0.1);
        }

        .probador-virtual-container .upload-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .probador-virtual-container .dropdown-menu {
            padding: 0;
            border-radius: 8px;
            box-shadow: var(--shadow-medium);
        }

        .probador-virtual-container .dropdown-item {
            padding: 12px 16px;
            transition: all 0.2s;
        }

        .probador-virtual-container.dropdown-item:hover {
            background: var(--accent-2);
            color: white;
        }

        .probador-virtual-container #tryOnImagesGrid .item {
            cursor: pointer;
            transition: all 0.3s;
        }

        .probador-virtual-container #tryOnImagesGrid .item:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-medium);
        }

        .probador-virtual-container #tryOnImagesGrid .image-overlay {
            background: rgba(59, 130, 246, 0.8);
            opacity: 0;
        }

        .probador-virtual-container #tryOnImagesGrid .item:hover .image-overlay {
            opacity: 1;
        }

        .probador-virtual-container button:focus {
            outline: none;
            box-shadow: none;
        }

        /* Admin */
        .probador-virtual-container .admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .probador-virtual-container .stat-card {
            background: var(--bg-2);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1.5rem;
            transition: all 0.3s;
        }

        .probador-virtual-container .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }

        .probador-virtual-container .stat-title {
            font-size: 0.875rem;
            color: var(--txt-2);
            margin-bottom: 0.5rem;
        }

        .probador-virtual-container .stat-value {
            font-size: 2rem;
            font-weight: 600;
            color: var(--txt-1);
        }

        .probador-virtual-container .admin-table {
            background: var(--bg-2);
            border-radius: 8px;
            overflow: hidden;
        }

        .probador-virtual-container .admin-table th {
            background: var(--bg-3);
            color: var(--txt-1);
            font-weight: 600;
            padding: 1rem;
            border-bottom: 2px solid var(--border);
        }

        .probador-virtual-container .admin-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            color: var(--txt-2);
        }

        .probador-virtual-container .admin-table tr:hover {
            background: var(--bg-3);
        }

        .probador-virtual-container .delete-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: all 0.3s;
        }

        .probador-virtual-container .delete-btn:hover {
            background: #dc2626;
            transform: scale(1.05);
        }

        .probador-virtual-container .delete-all-btn {
            background: #dc2626;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .probador-virtual-container .delete-all-btn:hover {
            background: #b91c1c;
            transform: scale(1.05);
        }

    </style>
</head>
<body>
    <div class="probador-virtual-container">
        <div class="px-2 pt-3 pb-1">
            <div class="px-2">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center py-2">
                        <h5 class="m-0"><i class="fas fa-magic"></i> Probador Virtual con IA</h5>
                        <div class="navbar-nav ml-auto d-flex flex-row">
                            {{-- @can("plm.diseno.probador-virtual-ia.virtual-model") --}}
                            <a class="nav-link {{ request()->routeIs('virtual-model') ? 'active' : '' }}" href="{{ route('virtual-model') }}">
                                <i class="fas fa-user-plus"></i> Modelo Virtual
                            </a>
                            {{-- @endcan --}}

                            {{-- @can("plm.diseno.probador-virtual-ia.virtual-try-on") --}}
                            <a class="nav-link {{ request()->routeIs('virtual-try-on') ? 'active' : '' }}" href="{{ route('virtual-try-on') }}">
                                <i class="fas fa-tshirt"></i> Probador Virtual
                            </a>
                            {{-- @endcan --}}
                            {{-- @can("plm.diseno.probador-virtual-ia.image-to-video") --}}
                            <a class="nav-link {{ request()->routeIs('image-to-video') ? 'active' : '' }}" href="{{ route('image-to-video') }}">
                                <i class="fas fa-video"></i> Imagen a Video
                            </a>
                            {{-- @endcan --}}
                            <!-- Nuevo enlace para el panel de administración -->
                            {{-- @can("plm.diseno.probador-virtual-ia.admin") --}}
                            <a class="nav-link {{ request()->routeIs('admin.generations') ? 'active' : '' }}" href="{{ route('admin.generations') }}">
                                <i class="fas fa-cog"></i> Admin
                            </a>
                            {{-- @endcan --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="main-content px-2">
            @yield('content')
        </div>

        <button onclick="topFunction()" class="goBackUp" id="goBackUp" title="Volver arriba">
            <i class="fas fa-level-up-alt"></i>
        </button>
    </div>

    <script>
        document.querySelector("body").classList.add("sidebar-collapse");

        // Get the button:
        let mybutton = document.getElementById("goBackUp");
        // When the user scrolls down 20px from the top of the document, show the button
        window.onscroll = function() {scrollFunction()};

        function scrollFunction() {
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            mybutton.style.display = "block";
        } else {
            mybutton.style.display = "none";
        }
        }

        // When the user clicks on the button, scroll to the top of the document
        function topFunction() {
            window.scrollTo({top: 0, behavior: 'smooth'});
        }
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
