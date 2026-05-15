<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Editor de Camins – Circuit de Barcelona-Catalunya</title>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --color-primary: #6D1A1A;
            --color-primary-dark: #4A1010;
            --color-primary-light: #8B2020;
            --color-bg: #F5F5F5;
            --color-white: #FFFFFF;
            --color-text: #333333;
            --color-text-light: #666666;

            /* Path status colors */
            --color-abierto: #E53935;
            --color-obras: #F5C242;
            --color-staff: #42A5F5;
        }

        html, body {
            height: 100%;
            overflow: hidden;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: var(--color-bg);
            color: var(--color-text);
            display: flex;
            flex-direction: column;
        }

        /* ===== Top Bar ===== */
        .top-bar {
            width: 100%;
            background-color: var(--color-primary);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            z-index: 1000;
            flex-shrink: 0;
        }

        .top-bar__title {
            color: var(--color-white);
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .top-bar__actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            font-size: 13px;
            font-weight: 500;
            color: var(--color-white);
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 6px;
            text-decoration: none;
            transition: background 0.2s;
            font-family: 'Roboto', sans-serif;
            cursor: pointer;
        }

        .btn-back:hover {
            background: rgba(255,255,255,0.25);
        }

        /* ===== Main Layout ===== */
        .editor-layout {
            flex: 1;
            display: flex;
            min-height: 0;
            position: relative;
        }

        /* ===== Left Sidebar — Status Legend ===== */
        .sidebar-left {
            width: 100px;
            background: rgba(200, 230, 180, 0.92);
            border-right: 2px solid #A5C87D;
            display: none; /* Hidden by default, shown when pencil active */
            flex-direction: column;
            padding: 16px 8px;
            gap: 20px;
            z-index: 900;
            flex-shrink: 0;
        }

        .sidebar-left.show {
            display: flex;
        }

        .status-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            cursor: pointer;
            padding: 8px 4px;
            border-radius: 8px;
            transition: all 0.2s;
            border: 2px solid transparent;
        }

        .status-item:hover {
            background: rgba(255,255,255,0.5);
        }

        .status-item.active {
            background: rgba(255,255,255,0.7);
            border-color: var(--color-primary);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .status-item__label {
            font-size: 11px;
            font-weight: 700;
            color: var(--color-text);
            text-align: center;
            line-height: 1.2;
        }

        .status-item__color {
            width: 36px;
            height: 28px;
            border-radius: 4px;
            border: 2px solid rgba(0,0,0,0.15);
            transition: transform 0.2s;
        }

        .status-item:hover .status-item__color {
            transform: scale(1.1);
        }

        /* ===== Map Container ===== */
        #map {
            flex: 1;
            min-width: 0;
        }

        /* Leaflet classes for cursor overrides when drawing */
        .leaflet-container.crosshair-cursor-enabled {
            cursor: crosshair !important;
        }
        .leaflet-container.pointer-cursor-enabled {
            cursor: pointer !important;
        }

        /* Path hover highlight */
        .path-highlight {
            filter: drop-shadow(0 0 4px yellow);
            stroke-width: 8 !important;
        }

        /* ===== Right Sidebar — Tools ===== */
        .sidebar-right {
            width: 70px;
            background: rgba(200, 230, 180, 0.92);
            border-left: 2px solid #A5C87D;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 16px 8px;
            gap: 12px;
            z-index: 900;
            flex-shrink: 0;
        }

        .tool-btn {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            border: 2px solid rgba(0,0,0,0.1);
            background: var(--color-white);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .tool-btn:hover {
            background: #f0f0f0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }

        .tool-btn.active {
            background: var(--color-primary);
            border-color: var(--color-primary-dark);
            box-shadow: 0 2px 8px rgba(109,26,26,0.4);
        }

        .tool-btn.active svg {
            fill: var(--color-white);
        }

        .tool-btn svg {
            width: 26px;
            height: 26px;
            fill: var(--color-text);
            transition: fill 0.2s;
        }

        .tool-separator {
            width: 36px;
            height: 2px;
            background: rgba(0,0,0,0.15);
            border-radius: 1px;
            margin: 4px 0;
        }

        /* Save button */
        .btn-save {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            background: #4CAF50;
            border: 2px solid #388E3C;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 6px rgba(76,175,80,0.3);
            margin-top: auto;
        }

        .btn-save:hover {
            background: #388E3C;
            transform: translateY(-1px);
        }

        .btn-save:active {
            transform: translateY(0);
        }

        .btn-save svg {
            width: 24px;
            height: 24px;
            fill: var(--color-white);
        }

        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            font-weight: 700;
        }
        .loading-overlay.show { display: flex; }

        /* ===== Toast Notifications ===== */
        .toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: var(--color-primary);
            color: var(--color-white);
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 4px 16px rgba(0,0,0,0.3);
            z-index: 2000;
            transition: transform 0.3s ease;
            pointer-events: none;
        }

        .toast.show { transform: translateX(-50%) translateY(0); }
        .toast.success { background: #388E3C; }
        .toast.error { background: #C62828; }

        /* Path Selection Popup */
        .path-popup {
            text-align: center;
        }
        .path-popup button {
            background: #E53935;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 8px;
            font-family: inherit;
        }
        .path-popup button:hover { background: #C62828; }

        /* Marker Modal */
        .marker-modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); z-index: 10000;
            display: none; align-items: center; justify-content: center;
        }
        .marker-modal-overlay.show { display: flex; }
        .marker-modal {
            background: white; padding: 20px; border-radius: 8px; width: 300px;
            display: flex; flex-direction: column; gap: 10px;
        }
        .marker-modal h3 { font-size: 16px; margin-bottom: 5px; color: var(--color-primary); }
        .marker-modal input, .marker-modal select { padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: inherit; }
        .marker-modal button { padding: 8px; border: none; border-radius: 4px; cursor: pointer; color: white; font-weight: bold; }
        .btn-cancel { background: #999; }
        .btn-cancel:hover { background: #777; }
        .btn-save-marker { background: var(--color-primary); }
        .btn-save-marker:hover { background: var(--color-primary-dark); }

        /* Emoji Leaflet DivIcon */
        .emoji-marker {
            background: white;
            border: 2px solid var(--color-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        /* ===== Marker Management Panel ===== */
        .marker-mgmt-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.55); z-index: 10001;
            display: none; align-items: center; justify-content: center;
            backdrop-filter: blur(3px);
        }
        .marker-mgmt-overlay.show { display: flex; }

        .marker-mgmt-panel {
            background: #fff; border-radius: 12px; width: 480px; max-width: 95vw;
            max-height: 85vh; display: flex; flex-direction: column;
            box-shadow: 0 16px 48px rgba(0,0,0,0.35);
            animation: panelSlideIn 0.25s ease-out;
            overflow: hidden;
        }
        @keyframes panelSlideIn {
            from { transform: translateY(30px) scale(0.97); opacity: 0; }
            to { transform: translateY(0) scale(1); opacity: 1; }
        }

        .marker-mgmt-header {
            padding: 16px 20px; background: var(--color-primary); color: #fff;
            display: flex; align-items: center; justify-content: space-between;
        }
        .marker-mgmt-header h3 { font-size: 15px; font-weight: 700; }
        .marker-mgmt-close {
            background: rgba(255,255,255,0.2); border: none; color: #fff;
            width: 30px; height: 30px; border-radius: 50%; cursor: pointer;
            font-size: 16px; display: flex; align-items: center; justify-content: center;
            transition: background 0.2s;
        }
        .marker-mgmt-close:hover { background: rgba(255,255,255,0.35); }

        .marker-mgmt-filters {
            display: flex; flex-wrap: wrap; gap: 6px; padding: 14px 20px;
            border-bottom: 1px solid #eee; background: #fafafa;
        }
        .filter-chip {
            padding: 6px 12px; border-radius: 20px; border: 1px solid #ddd;
            background: #fff; cursor: pointer; font-size: 12px; font-weight: 600;
            font-family: inherit; transition: all 0.2s; display: flex;
            align-items: center; gap: 4px; color: #555;
        }
        .filter-chip:hover { border-color: var(--color-primary); color: var(--color-primary); }
        .filter-chip.active {
            background: var(--color-primary); color: #fff;
            border-color: var(--color-primary);
        }

        .marker-mgmt-list {
            flex: 1; overflow-y: auto; padding: 10px 20px;
        }
        .marker-mgmt-list::-webkit-scrollbar { width: 6px; }
        .marker-mgmt-list::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }

        .marker-list-empty {
            text-align: center; color: #999; padding: 40px 20px;
            font-size: 14px;
        }

        .marker-list-item {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 14px; border-radius: 8px; margin-bottom: 6px;
            border: 1px solid #eee; transition: all 0.25s;
            background: #fff;
        }
        .marker-list-item:hover { border-color: #ccc; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }

        .marker-list-item.inactive {
            opacity: 0.45; background: #f8f8f8;
        }
        .marker-list-item.inactive .mli-emoji { filter: grayscale(1); }

        .mli-emoji { font-size: 24px; flex-shrink: 0; transition: filter 0.25s; }
        .mli-info { flex: 1; min-width: 0; }
        .mli-name { font-size: 13px; font-weight: 700; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .mli-desc { font-size: 11px; color: #888; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }
        .mli-coords { font-size: 10px; color: #aaa; margin-top: 2px; }

        /* Toggle switch */
        .toggle-switch {
            position: relative; width: 42px; height: 24px; flex-shrink: 0;
        }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-slider {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: #ccc; border-radius: 12px; cursor: pointer;
            transition: background 0.3s;
        }
        .toggle-slider::before {
            content: ''; position: absolute; width: 18px; height: 18px;
            left: 3px; bottom: 3px; background: #fff; border-radius: 50%;
            transition: transform 0.3s;
        }
        .toggle-switch input:checked + .toggle-slider { background: #4CAF50; }
        .toggle-switch input:checked + .toggle-slider::before { transform: translateX(18px); }

        .marker-mgmt-footer {
            padding: 12px 20px; border-top: 1px solid #eee;
            background: #fafafa; display: flex; justify-content: space-between;
            align-items: center; font-size: 12px; color: #888;
        }
    </style>
</head>
<body>
    <div class="loading-overlay" id="loading-overlay">Guardant...</div>

    <!-- Modal Marcador -->
    <div class="marker-modal-overlay" id="marker-modal-overlay">
        <div class="marker-modal">
            <h3>Nou Marcador</h3>
            <label>Nom:</label>
            <input type="text" id="marker-nombre" placeholder="Ex: Porta 3" />
            <label>Descripció:</label>
            <input type="text" id="marker-desc" placeholder="Informació opcional" />
            <label>Tipus:</label>
            <select id="marker-tipo">
                <option value="1">🚪 Entrades</option>
                <option value="2">🛒 Botigues</option>
                <option value="3">🅿️ Aparcaments</option>
                <option value="4">🚾 Banys</option>
                <option value="5">💺 Seients</option>
                <option value="6">🟣 Punt Violeta</option>
                <option value="7">♻️ Reciclatge</option>
            </select>
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <button class="btn-cancel" id="btn-cancel-marker">Cancel·lar</button>
                <button class="btn-save-marker" id="btn-save-marker">Guardar Marcador</button>
            </div>
        </div>
    </div>

    <!-- Panel Gestión Marcadores Activos -->
    <div class="marker-mgmt-overlay" id="marker-mgmt-overlay">
        <div class="marker-mgmt-panel">
            <div class="marker-mgmt-header">
                <h3>📋 Gestió de Marcadors</h3>
                <button class="marker-mgmt-close" id="marker-mgmt-close">✕</button>
            </div>
            <div class="marker-mgmt-filters" id="marker-mgmt-filters">
                <!-- Filter chips se generan con JS -->
            </div>
            <div class="marker-mgmt-list" id="marker-mgmt-list">
                <div class="marker-list-empty">Selecciona una categoria per veure els seus marcadors</div>
            </div>
            <div class="marker-mgmt-footer">
                <span id="marker-mgmt-count">0 marcadors</span>
                <span style="font-style:italic;">Dades via API REST</span>
            </div>
        </div>
    </div>

    <!-- Top Bar -->
    <div class="top-bar">
        <span class="top-bar__title">✏️ Editor de Camins</span>
        <div class="top-bar__actions">
            <a href="{{ url('/') }}" class="btn-back">← Inici</a>
            <a href="{{ route('map.show') }}" class="btn-back">🗺️ Veure Mapa</a>
        </div>
    </div>

    <!-- Editor Layout -->
    <div class="editor-layout">
        <!-- Left Sidebar: Status/Color Selector -->
        <div class="sidebar-left" id="sidebar-left">
            <div class="status-item active" data-estado="abierto" data-color="#E53935" id="status-abierto" title="Camí obert per a usuaris">
                <div class="status-item__color" style="background-color: #E53935;"></div>
                <span class="status-item__label">Obert</span>
            </div>
            <div class="status-item" data-estado="obras" data-color="#F5C242" id="status-obras" title="En obres - No s'hi pot passar">
                <div class="status-item__color" style="background-color: #F5C242;"></div>
                <span class="status-item__label">OBRES</span>
            </div>
            <div class="status-item" data-estado="staff" data-color="#42A5F5" id="status-staff" title="Només per a personal (staff)">
                <div class="status-item__color" style="background-color: #42A5F5;"></div>
                <span class="status-item__label">Staff</span>
            </div>
        </div>

        <!-- Map -->
        <div id="map"></div>

        <!-- Right Sidebar: Tools -->
        <div class="sidebar-right" id="sidebar-right">
            <!-- Pencil / Freehand Draw tool -->
            <div class="tool-btn" id="tool-draw" title="Dibuixar lliurement (Llapis)">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                </svg>
            </div>

            <!-- Hand / Pan tool -->
            <div class="tool-btn active" id="tool-pan" title="Moure mapa (Mà)">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 24h-6.55c-1.08 0-2.14-.45-2.89-1.23l-5.5-6.41 1.36-1.33c.38-.37.88-.58 1.41-.58h.2l3.97.7V3.5C10 2.67 10.67 2 11.5 2s1.5.67 1.5 1.5v7h.5c.15 0 .29.02.43.05l5.76 1.45c.87.22 1.49.99 1.49 1.89V22c0 1.1-.9 2-2 2h-.18zM5.13 17.27L9.91 22.8c.38.43.91.7 1.54.7H18c.55 0 1-.45 1-1v-7.61l-5.57-1.39H13V3.5c0-.28-.22-.5-.5-.5s-.5.22-.5.5v10.5H10l-4.67-.86-.2.13z"/>
                </svg>
            </div>

            <!-- Select / Cursor tool -->
            <div class="tool-btn" id="tool-select" title="Seleccionar camins (Cursor)">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7 2l12 11.2-5.8.5 3.3 7.3-2.25.9-3.2-7.4L7 18z"/>
                </svg>
            </div>

            <!-- Marker / Pin tool -->
            <div class="tool-btn" id="tool-marker" title="Afegir Marcador (Pin)">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                </svg>
            </div>

            <!-- Marker Management / Active markers -->
            <div class="tool-btn" id="tool-mgmt" title="Gestionar Marcadors Actius">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/>
                </svg>
            </div>

            <div class="tool-separator"></div>

            <!-- Save button -->
            <div class="btn-save" id="btn-save" title="Guardar tots els canvis al Servidor">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Toast notification -->
    <div class="toast" id="toast"></div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.1/firebase-app.js";
        import { getFirestore, collection, getDocs, addDoc, deleteDoc, doc, GeoPoint } from "https://www.gstatic.com/firebasejs/10.8.1/firebase-firestore.js";

        // Configuración real obtenida del google-services.json
        const firebaseConfig = {
            apiKey: "AIzaSyAClNt3aSPhe_eUA__wPAjk29mUPOgxoU0",
            authDomain: "prototipo-circuito.firebaseapp.com",
            projectId: "prototipo-circuito",
            storageBucket: "prototipo-circuito.firebasestorage.app",
            messagingSenderId: "151738352131",
            appId: "1:151738352131:android:58291ac8ae6eb5d2ea0436"
        };

        const app = initializeApp(firebaseConfig);
        const db = getFirestore(app);

        // ============================================================
        // State
        // ============================================================
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

        const STATUS_COLORS = {
            abierto: '#E53935',
            obras:   '#F5C242',
            staff:   '#42A5F5'
        };

        let currentEstado = 'abierto';
        let currentColor  = STATUS_COLORS.abierto;
        let currentTool   = 'pan'; // 'pan', 'draw', 'select', 'marker'

        // Freehand Drawing state
        let isDrawing = false;
        let currentDrawLine = null;

        // Marcadores Firebase State
        let pendingMarkerLatLng = null;
        const firebaseMarkers = {}; // Para guardar refs a los Layer de validación y borrarlos

        // Map setup
        const map = L.map('map', {
            zoomControl: true,
            doubleClickZoom: false // disable to prevent zooming while drawing quickly
        });

        const mapContainer = map.getContainer();

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // ============================================================
        // FeatureGroup for all paths (existing + new drawn ones)
        // ============================================================
        const allPathsGroup = new L.FeatureGroup();
        map.addLayer(allPathsGroup);

        // Load Base GeoJSON from DB
        const baseGeojsonData = {!! $baseGeojson !!};

        function addPathToGroup(feature, existingColor) {
            const estado = feature.properties?.estado || 'abierto';
            const color = existingColor || STATUS_COLORS[estado] || STATUS_COLORS.abierto;
            
            const layer = L.geoJSON(feature, {
                style: {
                    color: color,
                    weight: 5,
                    opacity: 0.85
                }
            });

            // Each path instance (could be multiple if feature is FeatureCollection)
            layer.eachLayer(l => {
                // Store properties attached to the individual layer
                l.feature = l.feature || {};
                l.feature.type = 'Feature';
                l.feature.properties = l.feature.properties || {};
                l.feature.properties.estado = estado;
                l.feature.properties.color = color;

                allPathsGroup.addLayer(l);
                attachSelectionEvents(l);
            });
        }

        if (baseGeojsonData && baseGeojsonData.features) {
            baseGeojsonData.features.forEach(feature => {
                addPathToGroup(feature);
            });
        } else if (baseGeojsonData && baseGeojsonData.type === 'Feature') {
            addPathToGroup(baseGeojsonData);
        } else {
            map.setView([41.566, 2.258], 15);
        }

        if (allPathsGroup.getLayers().length > 0 && allPathsGroup.getBounds().isValid()) {
            map.fitBounds(allPathsGroup.getBounds(), { padding: [50, 50] });
        }

        // ============================================================
        // Freehand Drawing Logic
        // ============================================================
        map.on('mousedown', function (e) {
            if (currentTool !== 'draw') return;

            // In Leaflet, dragging prevents nice freehand, so disable it
            map.dragging.disable();
            isDrawing = true;

            // Start a new line
            currentDrawLine = L.polyline([e.latlng], {
                color: currentColor,
                weight: 5,
                opacity: 0.85,
                lineCap: 'round',
                lineJoin: 'round'
            }).addTo(allPathsGroup);
        });

        map.on('mousemove', function (e) {
            if (!isDrawing || currentTool !== 'draw' || !currentDrawLine) return;
            currentDrawLine.addLatLng(e.latlng);
        });

        // Lógica click mapa para el Marcador (Pin)
        const markerModalOverlay = document.getElementById('marker-modal-overlay');
        const btnCancelMarker = document.getElementById('btn-cancel-marker');
        const btnSaveMarkerBtn = document.getElementById('btn-save-marker');

        map.on('click', function(e) {
            if (currentTool === 'marker') {
                pendingMarkerLatLng = e.latlng;
                document.getElementById('marker-nombre').value = '';
                document.getElementById('marker-desc').value = '';
                document.getElementById('marker-tipo').value = '1';
                markerModalOverlay.classList.add('show');
            }
        });

        btnCancelMarker.addEventListener('click', () => {
            markerModalOverlay.classList.remove('show');
            pendingMarkerLatLng = null;
        });

        btnSaveMarkerBtn.addEventListener('click', async () => {
            if (!pendingMarkerLatLng) return;
            const nombre = document.getElementById('marker-nombre').value.trim();
            const desc = document.getElementById('marker-desc').value.trim();
            const tipo = parseInt(document.getElementById('marker-tipo').value) || 1;

            if (!nombre) {
                alert('El nom és obligatori');
                return;
            }

            const lat = pendingMarkerLatLng.lat;
            const lng = pendingMarkerLatLng.lng;
            markerModalOverlay.classList.remove('show');
            const loadingOverlay = document.getElementById('loading-overlay');
            loadingOverlay.classList.add('show');

            try {
                const response = await fetch('/api/marcadores', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        titulo: nombre,
                        descripcion: desc,
                        tipo: tipo,
                        latitud: lat,
                        longitud: lng,
                        activo: true
                    })
                });

                if (!response.ok) throw new Error('Error en API');
                const data = await response.json();

                createMarkerOnMap(data.id, lat, lng, nombre, desc, tipo, true);
                showToast('Marcador guardat', 'success');
                if (currentTool === 'mgmt') refreshMgmtList();
            } catch (err) {
                console.error("Error adding document: ", err);
                showToast('Error en guardar el marcador', 'error');
            } finally {
                loadingOverlay.classList.remove('show');
                pendingMarkerLatLng = null;
                setTool('pan');
            }
        });

        window.deleteApiMarker = async function(id) {
            if (confirm('Estàs segur que vols eliminar aquest marcador?')) {
                const loadingOverlay = document.getElementById('loading-overlay');
                loadingOverlay.classList.add('show');
                try {
                    const res = await fetch('/api/marcadores/' + id, { method: 'DELETE' });
                    if (!res.ok) throw new Error('Error deleting');
                    
                    if (firebaseMarkers[id]) {
                        map.removeLayer(firebaseMarkers[id]);
                        delete firebaseMarkers[id];
                    }
                    showToast('Marcador eliminat', 'success');
                    map.closePopup();
                    if (currentTool === 'mgmt') refreshMgmtList();
                } catch (e) {
                    console.error('Error deleting doc', e);
                    showToast('Error en eliminar', 'error');
                } finally {
                    loadingOverlay.classList.remove('show');
                }
            }
        };

        const EMOJIS = {
            1: '🚪',
            2: '🛒',
            3: '🅿️',
            4: '🚾',
            5: '💺',
            6: '🟣',
            7: '♻️'
        };

        const TXT_TIPOS = {
            1: 'Entrades',
            2: 'Botigues',
            3: 'Aparcaments',
            4: 'Banys',
            5: 'Seients',
            6: 'Punt Violeta',
            7: 'Reciclatge'
        };

        function createMarkerOnMap(id, lat, lng, nombre, descripcion, tipo, activo = true) {
            const emoji = EMOJIS[tipo] || '📍';
            const tipoNom = TXT_TIPOS[tipo] || 'Personalitzat';

            const customIcon = L.divIcon({
                className: 'emoji-marker',
                html: `<div style="${!activo ? 'filter: grayscale(1); opacity: 0.5;' : ''}">${emoji}</div>`,
                iconSize: [36, 36],
                iconAnchor: [18, 18],
                popupAnchor: [0, -18]
            });

            const m = L.marker([lat, lng], { icon: customIcon }).addTo(map);
            
            m.on('click', function(e) {
                if (currentTool === 'select') {
                    const popupContent = `
                        <div class="path-popup">
                            <strong>${emoji} ${nombre}</strong> ${!activo ? '<span style="color:red">(Inactiu)</span>' : ''}<br>
                            <small>${descripcion || 'Sense descripció'} (Tipus: ${tipoNom})</small><br>
                            <button style="background:#555;margin-top:10px;" onclick="deleteApiMarker('${id}')">Eliminar Marcador</button>
                        </div>
                    `;
                    m.bindPopup(popupContent).openPopup();
                }
            });

            m.markerData = { id, lat, lng, nombre, descripcion, tipo, activo };
            firebaseMarkers[id] = m;
        }

        async function loadApiMarkers() {
            try {
                const response = await fetch('/api/marcadores');
                const data = await response.json();
                data.forEach((marker) => {
                    createMarkerOnMap(
                        marker.id, 
                        parseFloat(marker.latitud), 
                        parseFloat(marker.longitud), 
                        marker.titulo || '', 
                        marker.descripcion || '', 
                        marker.tipo || 1,
                        marker.activo
                    );
                });
            } catch (e) {
                console.warn('Error loading markers from API:', e);
            }
        }
        
        loadApiMarkers();

        // Use map mouseup AND window mouseup to ensure it completes even if mouse leaves map
        function finishDrawing() {
            if (!isDrawing || currentTool !== 'draw' || !currentDrawLine) return;
            
            isDrawing = false;
            map.dragging.enable();

            // Give it GeoJSON properties
            currentDrawLine.feature = currentDrawLine.feature || {};
            currentDrawLine.feature.type = 'Feature';
            currentDrawLine.feature.properties = {
                estado: currentEstado,
                color: currentColor
            };

            attachSelectionEvents(currentDrawLine);
            currentDrawLine = null;
        }

        map.on('mouseup', finishDrawing);
        window.addEventListener('mouseup', function(e) {
            if (isDrawing) finishDrawing();
        });

        // ============================================================
        // Selection / Arrow logic
        // ============================================================
        function attachSelectionEvents(layer) {
            layer.on('click', function(e) {
                if (currentTool !== 'select') return;
                
                // Allow deletion via popup
                const label = layer.feature?.properties?.estado || 'Desconocido';
                const popupContent = `
                    <div class="path-popup">
                        <strong>Camí: ${label.toUpperCase()}</strong><br>
                        <button onclick="deleteLayer(${L.stamp(layer)})">Eliminar Camí</button>
                    </div>
                `;
                
                layer.bindPopup(popupContent).openPopup(e.latlng);
            });

            // Hover effects
            layer.on('mouseover', function () {
                if (currentTool === 'select') {
                    layer.setStyle({ weight: 8 });
                    layer.getElement()?.style.setProperty('filter', 'drop-shadow(0 0 5px rgba(255,255,255,0.8))');
                }
            });
            layer.on('mouseout', function () {
                if (currentTool === 'select') {
                    layer.setStyle({ weight: 5 });
                    layer.getElement()?.style.setProperty('filter', 'none');
                }
            });
        }

        // Must be global for the popup button to see it
        window.deleteLayer = function(stampId) {
            allPathsGroup.eachLayer(layer => {
                if (L.stamp(layer) === stampId) {
                    allPathsGroup.removeLayer(layer);
                }
            });
            map.closePopup();
        };

        // ============================================================
        // Gestión de Marcadores (Panel)
        // ============================================================
        const mgmtOverlay = document.getElementById('marker-mgmt-overlay');
        const btnCloseMgmt = document.getElementById('marker-mgmt-close');
        const filtersContainer = document.getElementById('marker-mgmt-filters');
        const listContainer = document.getElementById('marker-mgmt-list');
        const mgmtCount = document.getElementById('marker-mgmt-count');
        let currentMgmtFilter = null;
        let mgmtMarkersData = [];

        btnCloseMgmt.addEventListener('click', () => {
            mgmtOverlay.classList.remove('show');
            setTool('pan');
        });

        async function openMgmtPanel() {
            mgmtOverlay.classList.add('show');
            buildFilters();
            await refreshMgmtList();
        }

        function buildFilters() {
            filtersContainer.innerHTML = '';
            
            // "Todos" chip
            const btnAll = document.createElement('button');
            btnAll.className = 'filter-chip' + (currentMgmtFilter === null ? ' active' : '');
            btnAll.innerHTML = 'Tots';
            btnAll.onclick = () => { currentMgmtFilter = null; buildFilters(); renderMgmtList(); };
            filtersContainer.appendChild(btnAll);

            // Chips por tipo
            Object.keys(TXT_TIPOS).forEach(tipo => {
                const t = parseInt(tipo);
                const btn = document.createElement('button');
                btn.className = 'filter-chip' + (currentMgmtFilter === t ? ' active' : '');
                btn.innerHTML = `${EMOJIS[t]} ${TXT_TIPOS[t]}`;
                btn.onclick = () => { currentMgmtFilter = t; buildFilters(); renderMgmtList(); };
                filtersContainer.appendChild(btn);
            });
        }

        async function refreshMgmtList() {
            listContainer.innerHTML = '<div class="marker-list-empty">Carregant...</div>';
            try {
                const res = await fetch('/api/marcadores');
                mgmtMarkersData = await res.json();
                renderMgmtList();
            } catch (e) {
                listContainer.innerHTML = '<div class="marker-list-empty" style="color:red">Error en carregar marcadors</div>';
            }
        }

        function renderMgmtList() {
            listContainer.innerHTML = '';
            
            let filtered = mgmtMarkersData;
            if (currentMgmtFilter !== null) {
                filtered = mgmtMarkersData.filter(m => m.tipo === currentMgmtFilter);
            }
            
            mgmtCount.textContent = `${filtered.length} marcadors`;

            if (filtered.length === 0) {
                listContainer.innerHTML = '<div class="marker-list-empty">No hi ha marcadors en aquesta categoria</div>';
                return;
            }

            filtered.forEach(m => {
                const div = document.createElement('div');
                div.className = 'marker-list-item' + (!m.activo ? ' inactive' : '');
                
                const emoji = EMOJIS[m.tipo] || '📍';
                const desc = m.descripcion || 'Sense descripció';
                
                div.innerHTML = `
                    <div class="mli-emoji">${emoji}</div>
                    <div class="mli-info">
                        <div class="mli-name">${m.titulo}</div>
                        <div class="mli-desc">${desc}</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" onchange="toggleMarkerActive(${m.id}, this.checked)" ${m.activo ? 'checked' : ''}>
                        <span class="toggle-slider"></span>
                    </label>
                `;
                listContainer.appendChild(div);
            });
        }

        window.toggleMarkerActive = async function(id, isChecked) {
            try {
                const res = await fetch('/api/marcadores/' + id, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: JSON.stringify({ activo: isChecked })
                });
                if (!res.ok) throw new Error('Update failed');
                
                if (firebaseMarkers[id]) {
                    const mLayer = firebaseMarkers[id];
                    const data = mLayer.markerData;
                    data.activo = isChecked;
                    
                    map.removeLayer(mLayer);
                    createMarkerOnMap(data.id, data.lat, data.lng, data.nombre, data.descripcion, data.tipo, isChecked);
                }
                
                const mData = mgmtMarkersData.find(x => x.id === id);
                if (mData) mData.activo = isChecked;
                renderMgmtList();
                
            } catch(e) {
                console.error(e);
                alert('Error en actualitzar el marcador');
                refreshMgmtList();
            }
        };

        // ============================================================
        // Tools & Sidebar Logic
        // ============================================================
        const toolDraw   = document.getElementById('tool-draw');
        const toolPan    = document.getElementById('tool-pan');
        const toolSelect = document.getElementById('tool-select');
        const toolMarker = document.getElementById('tool-marker');
        const toolMgmt   = document.getElementById('tool-mgmt');
        const sidebarLeft = document.getElementById('sidebar-left');

        function setTool(tool) {
            currentTool = tool;

            // Update button UI
            toolDraw.classList.toggle('active', tool === 'draw');
            toolPan.classList.toggle('active', tool === 'pan');
            toolSelect.classList.toggle('active', tool === 'select');
            toolMarker.classList.toggle('active', tool === 'marker');
            if (toolMgmt) toolMgmt.classList.toggle('active', tool === 'mgmt');

            // Show/Hide Left Panel based on pencil tool
            if (tool === 'draw') {
                sidebarLeft.classList.add('show');
            } else {
                sidebarLeft.classList.remove('show');
            }

            // Map interaction modes
            if (tool === 'pan') {
                map.dragging.enable();
                mapContainer.classList.remove('crosshair-cursor-enabled', 'pointer-cursor-enabled');
            } else if (tool === 'draw') {
                map.dragging.disable();
                mapContainer.classList.remove('pointer-cursor-enabled');
                mapContainer.classList.add('crosshair-cursor-enabled');
                map.closePopup();
            } else if (tool === 'select') {
                map.dragging.enable();
                mapContainer.classList.remove('crosshair-cursor-enabled');
                mapContainer.classList.add('pointer-cursor-enabled');
            } else if (tool === 'marker') {
                map.dragging.disable();
                mapContainer.classList.remove('pointer-cursor-enabled');
                mapContainer.classList.add('crosshair-cursor-enabled');
                map.closePopup();
            } else if (tool === 'mgmt') {
                map.dragging.enable();
                mapContainer.classList.remove('crosshair-cursor-enabled');
                mapContainer.classList.add('pointer-cursor-enabled');
                openMgmtPanel();
            }
        }

        toolDraw.addEventListener('click', () => setTool('draw'));
        toolPan.addEventListener('click', () => setTool('pan'));
        toolSelect.addEventListener('click', () => setTool('select'));
        toolMarker.addEventListener('click', () => setTool('marker'));
        if (toolMgmt) toolMgmt.addEventListener('click', () => setTool('mgmt'));

        // Left Panel Color Selector
        const statusItems = document.querySelectorAll('.status-item');
        statusItems.forEach(item => {
            item.addEventListener('click', () => {
                statusItems.forEach(s => s.classList.remove('active'));
                item.classList.add('active');
                currentEstado = item.dataset.estado;
                currentColor  = item.dataset.color;
            });
        });

        // Initialize default tool
        setTool('pan');

        // ============================================================
        // Save Map to Supabase via Laravel MapController
        // ============================================================
        const btnSave = document.getElementById('btn-save');
        const loadingOverlay = document.getElementById('loading-overlay');

        btnSave.addEventListener('click', async () => {
            // Aggregate everything into a FeatureCollection
            const features = [];
            allPathsGroup.eachLayer(layer => {
                if (typeof layer.toGeoJSON === 'function') {
                    const geo = layer.toGeoJSON();
                    // Ensure the properties are kept
                    geo.properties = geo.properties || {};
                    if (layer.feature && layer.feature.properties) {
                        geo.properties.estado = layer.feature.properties.estado || 'abierto';
                        geo.properties.color = layer.feature.properties.color || STATUS_COLORS.abierto;
                    }
                    features.push(geo);
                }
            });

            const updatedGeojson = {
                type: 'FeatureCollection',
                features: features
            };

            loadingOverlay.classList.add('show');

            try {
                const response = await fetch('{{ route("map.save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ geojson: updatedGeojson })
                });

                const result = await response.json();

                if (result.success) {
                    showToast('Mapa pujat i actualitzat correctament.', 'success');
                } else {
                    showToast('Error en pujar el mapa.', 'error');
                    console.error(result.error);
                }
            } catch (err) {
                console.error('Save error:', err);
                showToast('Error en la connexió en guardar.', 'error');
            } finally {
                loadingOverlay.classList.remove('show');
            }
        });

        // Toast
        function showToast(message, type) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast ' + (type || '');
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3000);
        }

        // ============================================================
        // Handle Back-Forward Cache (BFCache)
        // ============================================================
        window.addEventListener('pageshow', function(event) {
            // Si la página se carga desde la caché del navegador (ej. botón "Atrás")
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</body>
</html>
