<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $nombre }} – Mapa</title>

    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Instrument Sans', system-ui, sans-serif;
            background: #0a0a0a;
            color: #ededec;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Header bar ── */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 2rem;
            background: #161615;
            border-bottom: 1px solid #3e3e3a;
        }

        .header h1 {
            font-size: 1.25rem;
            font-weight: 600;
            background: linear-gradient(135deg, #f53003, #ff6f3c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .5rem 1.25rem;
            font-size: .875rem;
            font-weight: 500;
            color: #ededec;
            background: #1b1b18;
            border: 1px solid #3e3e3a;
            border-radius: .25rem;
            text-decoration: none;
            transition: background .2s, border-color .2s;
        }
        .btn-back:hover { background: #3e3e3a; border-color: #62605b; }

        /* ── Map container ── */
        #map {
            flex: 1;
            min-height: 0;          /* allow flex shrink */
        }

        /* Leaflet dark tiles tint */
        .leaflet-tile-pane { filter: brightness(.85) contrast(1.1) saturate(.9); }

        /* Emoji Leaflet DivIcon */
        .emoji-marker {
            background: white;
            border: 2px solid #555;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
            color: #000;
        }

        .path-popup {
            text-align: center;
            color: #000;
        }

        /* ===== Custom Translate Select ===== */
        .custom-lang-selector {
            appearance: none;
            background: #1b1b18;
            border: 1px solid #3e3e3a;
            color: #ededec;
            border-radius: 6px;
            padding: 4px 24px 4px 10px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            outline: none;
            background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23ededec%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E");
            background-repeat: no-repeat;
            background-position: right 8px top 50%;
            background-size: 10px auto;
        }
        .custom-lang-selector option {
            color: #ededec;
            background: #1b1b18;
        }

        /* Hides the default google translate toolbar */
        .skiptranslate iframe {
            display: none !important;
        }
        body {
            top: 0px !important; 
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>🗺️ {{ $nombre }}</h1>
        <div style="display:flex; gap:15px; align-items:center;">
            <select class="custom-lang-selector" id="custom-lang-selector">
                <option value="ca">CAT</option>
                <option value="es">ESP</option>
                <option value="en">ENG</option>
                <option value="fr">FRA</option>
            </select>
            <a href="{{ url('/') }}" class="btn-back">← Inici</a>
        </div>
    </header>

    <div id="map"></div>

    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.1/firebase-app.js";
        import { getFirestore, collection, getDocs } from "https://www.gstatic.com/firebasejs/10.8.1/firebase-firestore.js";

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

        const geojsonData = {!! $geojson !!};

        // Create map
        const map = L.map('map');

        // Tile layer (OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Status colors matching the editor
        const STATUS_COLORS = {
            abierto: '#E53935',
            obras:   '#F5C242',
            staff:   '#42A5F5'
        };

        // Add GeoJSON layer
        const geoLayer = L.geoJSON(geojsonData, {
            style: function(feature) {
                const estado = feature.properties?.estado || 'abierto';
                const color = feature.properties?.color || STATUS_COLORS[estado] || STATUS_COLORS.abierto;
                
                return {
                    color: color,
                    weight: 5,
                    opacity: 0.85
                };
            },
            pointToLayer: function (feature, latlng) {
                const estado = feature.properties?.estado || 'abierto';
                const color = feature.properties?.color || STATUS_COLORS[estado] || STATUS_COLORS.abierto;

                return L.circleMarker(latlng, {
                    radius: 6,
                    fillColor: color,
                    color: color,
                    weight: 2,
                    fillOpacity: 0.9
                });
            },
            onEachFeature: function (feature, layer) {
                if (feature.properties) {
                    const props = Object.entries(feature.properties)
                        .map(([k, v]) => `<b>${k}</b>: ${v}`)
                        .join('<br>');
                    layer.bindPopup(props);
                }
            }
        }).addTo(map);

        // Fit bounds to the data
        if (geoLayer.getBounds().isValid()) {
            map.fitBounds(geoLayer.getBounds(), { padding: [30, 30] });
        } else {
            map.setView([41.566, 2.258], 15);
        }

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

        function createMarkerOnMap(id, lat, lng, nombre, descripcion, tipo) {
            const emoji = EMOJIS[tipo] || '📍';
            const tipoNom = TXT_TIPOS[tipo] || 'Personalitzat';

            // Crear divIcon con el emoji
            const customIcon = L.divIcon({
                className: 'emoji-marker',
                html: emoji,
                iconSize: [36, 36],
                iconAnchor: [18, 18],
                popupAnchor: [0, -18]
            });

            const m = L.marker([lat, lng], { icon: customIcon }).addTo(map);
            
            const popupContent = `
                <div class="path-popup">
                    <strong>${emoji} ${nombre}</strong><br>
                    <small>${descripcion} (Tipus: ${tipoNom})</small>
                </div>
            `;
            m.bindPopup(popupContent);
        }

        async function loadApiMarkers() {
            try {
                const response = await fetch('/api/marcadores?solo_activos=1');
                const data = await response.json();
                
                data.forEach((marker) => {
                    createMarkerOnMap(
                        marker.id, 
                        parseFloat(marker.latitud), 
                        parseFloat(marker.longitud), 
                        marker.titulo || '', 
                        marker.descripcion || '', 
                        marker.tipo || 1
                    );
                });
            } catch (e) {
                console.warn('Error loading markers from API:', e);
            }
        }
        
        loadApiMarkers();
    </script>
    <!-- Google Translate Widget Hidden -->
    <div id="google_translate_element" style="display: none;"></div>
    <script type="text/javascript">
    function googleTranslateElementInit() {
      new google.translate.TranslateElement({pageLanguage: 'ca', autoDisplay: false}, 'google_translate_element');
    }
    document.getElementById('custom-lang-selector').addEventListener('change', function() {
        var lang = this.value;
        var googleSelect = document.querySelector('.goog-te-combo');
        if (googleSelect) {
            googleSelect.value = lang;
            googleSelect.dispatchEvent(new Event('change'));
        }
    });
    </script>
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>
