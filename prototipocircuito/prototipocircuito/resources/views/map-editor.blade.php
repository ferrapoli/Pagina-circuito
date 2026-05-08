<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Editor de Caminos – Circuit de Barcelona-Catalunya</title>

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
    </style>
</head>
<body>
    <div class="loading-overlay" id="loading-overlay">Guardando...</div>

    <!-- Modal Marcador -->
    <div class="marker-modal-overlay" id="marker-modal-overlay">
        <div class="marker-modal">
            <h3>Nuevo Marcador</h3>
            <label>Nombre:</label>
            <input type="text" id="marker-nombre" placeholder="Ej: Puerta 3" />
            <label>Descripción:</label>
            <input type="text" id="marker-desc" placeholder="Información opcional" />
            <label>Tipo:</label>
            <select id="marker-tipo">
                <option value="1">🚪 Entradas</option>
                <option value="2">🛒 Tiendas</option>
                <option value="3">🅿️ Parkings</option>
                <option value="4">🚾 Baños</option>
                <option value="5">💺 Asientos</option>
                <option value="6">🟣 Punto Violeta</option>
                <option value="7">♻️ Reciclaje</option>
            </select>
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <button class="btn-cancel" id="btn-cancel-marker">Cancelar</button>
                <button class="btn-save-marker" id="btn-save-marker">Guardar Marcador</button>
            </div>
        </div>
    </div>

    <!-- Top Bar -->
    <div class="top-bar">
        <span class="top-bar__title">✏️ Editor de Caminos</span>
        <div class="top-bar__actions">
            <a href="{{ url('/') }}" class="btn-back">← Inicio</a>
            <a href="{{ route('map.show') }}" class="btn-back">🗺️ Ver Mapa</a>
        </div>
    </div>

    <!-- Editor Layout -->
    <div class="editor-layout">
        <!-- Left Sidebar: Status/Color Selector -->
        <div class="sidebar-left" id="sidebar-left">
            <div class="status-item active" data-estado="abierto" data-color="#E53935" id="status-abierto" title="Camino abierto para usuarios">
                <div class="status-item__color" style="background-color: #E53935;"></div>
                <span class="status-item__label">Abierto</span>
            </div>
            <div class="status-item" data-estado="obras" data-color="#F5C242" id="status-obras" title="En obras - No se puede pasar">
                <div class="status-item__color" style="background-color: #F5C242;"></div>
                <span class="status-item__label">OBReS</span>
            </div>
            <div class="status-item" data-estado="staff" data-color="#42A5F5" id="status-staff" title="Solo para staff">
                <div class="status-item__color" style="background-color: #42A5F5;"></div>
                <span class="status-item__label">Staff</span>
            </div>
        </div>

        <!-- Map -->
        <div id="map"></div>

        <!-- Right Sidebar: Tools -->
        <div class="sidebar-right" id="sidebar-right">
            <!-- Pencil / Freehand Draw tool -->
            <div class="tool-btn" id="tool-draw" title="Dibujar libremente (Lápiz)">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                </svg>
            </div>

            <!-- Hand / Pan tool -->
            <div class="tool-btn active" id="tool-pan" title="Mover mapa (Mano)">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 24h-6.55c-1.08 0-2.14-.45-2.89-1.23l-5.5-6.41 1.36-1.33c.38-.37.88-.58 1.41-.58h.2l3.97.7V3.5C10 2.67 10.67 2 11.5 2s1.5.67 1.5 1.5v7h.5c.15 0 .29.02.43.05l5.76 1.45c.87.22 1.49.99 1.49 1.89V22c0 1.1-.9 2-2 2h-.18zM5.13 17.27L9.91 22.8c.38.43.91.7 1.54.7H18c.55 0 1-.45 1-1v-7.61l-5.57-1.39H13V3.5c0-.28-.22-.5-.5-.5s-.5.22-.5.5v10.5H10l-4.67-.86-.2.13z"/>
                </svg>
            </div>

            <!-- Select / Cursor tool -->
            <div class="tool-btn" id="tool-select" title="Seleccionar caminos (Cursor)">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7 2l12 11.2-5.8.5 3.3 7.3-2.25.9-3.2-7.4L7 18z"/>
                </svg>
            </div>

            <!-- Marker / Pin tool -->
            <div class="tool-btn" id="tool-marker" title="Añadir Marcador de Firebase (Pin)">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                </svg>
            </div>

            <div class="tool-separator"></div>

            <!-- Save button -->
            <div class="btn-save" id="btn-save" title="Guardar todos los cambios al Servidor">
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
                alert('El nombre es obligatorio');
                return;
            }

            const lat = pendingMarkerLatLng.lat;
            const lng = pendingMarkerLatLng.lng;
            markerModalOverlay.classList.remove('show');
            const loadingOverlay = document.getElementById('loading-overlay');
            loadingOverlay.classList.add('show');

            try {
                // Instanciar guardado Firestore
                const docRef = await addDoc(collection(db, "marcadores_generales"), {
                    nombre: nombre,
                    descripcion: desc,
                    tipo: tipo,
                    usuario: "admin",
                    lugar: new GeoPoint(lat, lng)
                });

                // Añadir al mapa visualmente
                createMarkerOnMap(docRef.id, lat, lng, nombre, desc, tipo);
                showToast('Marcador guardado en Firebase', 'success');
            } catch (err) {
                console.error("Error adding document: ", err);
                showToast('Error al guardar marcador', 'error');
            } finally {
                loadingOverlay.classList.remove('show');
                pendingMarkerLatLng = null;
                setTool('pan');
            }
        });

        window.deleteFirebaseMarker = async function(id) {
            if (confirm('¿Estás seguro de eliminar este marcador de Firebase?')) {
                const loadingOverlay = document.getElementById('loading-overlay');
                loadingOverlay.classList.add('show');
                try {
                    await deleteDoc(doc(db, "marcadores_generales", id));
                    if (firebaseMarkers[id]) {
                        map.removeLayer(firebaseMarkers[id]);
                        delete firebaseMarkers[id];
                    }
                    showToast('Marcador eliminado', 'success');
                    map.closePopup();
                } catch (e) {
                    console.error('Error deleting doc', e);
                    showToast('Error al eliminar de Firebase', 'error');
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
            1: 'Entradas',
            2: 'Tiendas',
            3: 'Parkings',
            4: 'Baños',
            5: 'Asientos',
            6: 'Punto Violeta',
            7: 'Reciclaje'
        };

        function createMarkerOnMap(id, lat, lng, nombre, descripcion, tipo) {
            const emoji = EMOJIS[tipo] || '📍';
            const tipoNom = TXT_TIPOS[tipo] || 'Personalizado';

            // Crear divIcon con el emoji
            const customIcon = L.divIcon({
                className: 'emoji-marker',
                html: emoji,
                iconSize: [36, 36],
                iconAnchor: [18, 18],
                popupAnchor: [0, -18]
            });

            const m = L.marker([lat, lng], { icon: customIcon }).addTo(map);
            
            // Interaction logic for the marker (only delete when in select mode)
            m.on('click', function(e) {
                if (currentTool === 'select') {
                    const popupContent = `
                        <div class="path-popup">
                            <strong>${emoji} ${nombre}</strong><br>
                            <small>${descripcion} (Tipo: ${tipoNom})</small><br>
                            <button style="background:#555;margin-top:10px;" onclick="deleteFirebaseMarker('${id}')">Eliminar Marcador</button>
                        </div>
                    `;
                    m.bindPopup(popupContent).openPopup();
                }
            });

            firebaseMarkers[id] = m;
        }

        async function loadFirebaseMarkers() {
            try {
                const querySnapshot = await getDocs(collection(db, "marcadores_generales"));
                querySnapshot.forEach((docSnap) => {
                    const data = docSnap.data();
                    if (data.lugar && data.lugar.latitude) {
                        createMarkerOnMap(
                            docSnap.id, 
                            data.lugar.latitude, 
                            data.lugar.longitude, 
                            data.nombre || '', 
                            data.descripcion || '', 
                            data.tipo || ''
                        );
                    }
                });
            } catch (e) {
                console.warn('Error loading markers (puede que falte configuración):', e);
            }
        }
        
        loadFirebaseMarkers();

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
                        <strong>Camino: ${label.toUpperCase()}</strong><br>
                        <button onclick="deleteLayer(${L.stamp(layer)})">Eliminar Camino</button>
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
        // Tools & Sidebar Logic
        // ============================================================
        const toolDraw   = document.getElementById('tool-draw');
        const toolPan    = document.getElementById('tool-pan');
        const toolSelect = document.getElementById('tool-select');
        const toolMarker = document.getElementById('tool-marker');
        const sidebarLeft = document.getElementById('sidebar-left');

        function setTool(tool) {
            currentTool = tool;

            // Update button UI
            toolDraw.classList.toggle('active', tool === 'draw');
            toolPan.classList.toggle('active', tool === 'pan');
            toolSelect.classList.toggle('active', tool === 'select');
            toolMarker.classList.toggle('active', tool === 'marker');

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
                map.dragging.disable(); // Prevent map pan when dragging mouse to draw
                mapContainer.classList.remove('pointer-cursor-enabled');
                mapContainer.classList.add('crosshair-cursor-enabled');
                map.closePopup();
            } else if (tool === 'select') {
                map.dragging.enable();
                mapContainer.classList.remove('crosshair-cursor-enabled');
                mapContainer.classList.add('pointer-cursor-enabled');
            } else if (tool === 'marker') {
                map.dragging.disable(); // Para no arrastrar al poner pin
                mapContainer.classList.remove('pointer-cursor-enabled');
                mapContainer.classList.add('crosshair-cursor-enabled');
                map.closePopup();
            }
        }

        toolDraw.addEventListener('click', () => setTool('draw'));
        toolPan.addEventListener('click', () => setTool('pan'));
        toolSelect.addEventListener('click', () => setTool('select'));
        toolMarker.addEventListener('click', () => setTool('marker'));

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
                    showToast('Mapa subido y actualizado correctamente.', 'success');
                } else {
                    showToast('Error al subir el mapa.', 'error');
                    console.error(result.error);
                }
            } catch (err) {
                console.error('Save error:', err);
                showToast('Error en la conexión al guardar.', 'error');
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
