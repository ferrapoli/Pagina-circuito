<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Circuit de Barcelona-Catalunya</title>
    <meta name="description" content="Panel de administración del Circuit de Barcelona-Catalunya">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --color-primary: #6D1A1A;
            --color-primary-dark: #4A1010;
            --color-primary-light: #8B2020;
            --color-bg: #FAFAFA;
            --color-text: #333333;
            --color-text-light: #666666;
            --color-white: #FFFFFF;
            --color-border: #E0E0E0;
            --font-family: 'Roboto', sans-serif;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--color-bg);
            color: var(--color-text);
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ===== Top Bar ===== */
        .top-bar {
            width: 100%;
            background-color: var(--color-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 14px 20px;
            position: relative;
        }

        .top-bar__title {
            color: var(--color-white);
            font-size: 17px;
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        .top-bar__icon {
            position: absolute;
            right: 16px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .top-bar__icon svg {
            width: 16px;
            height: 16px;
            fill: var(--color-white);
        }

        .top-bar__menu {
            position: absolute;
            left: 16px;
            cursor: pointer;
        }

        .top-bar__menu svg {
            width: 22px;
            height: 22px;
            fill: var(--color-white);
        }

        /* ===== Main Content ===== */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 420px;
            padding: 0 24px;
        }

        /* ===== Admin Icon Area ===== */
        .admin-icon-area {
            margin-top: 50px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .admin-icon-area__shield {
            width: 120px;
            height: 120px;
            position: relative;
        }

        .admin-icon-area__shield svg {
            width: 100%;
            height: 100%;
        }

        /* ===== Heading ===== */
        .page-heading {
            text-align: center;
            margin-bottom: 50px;
        }

        .page-heading__title {
            font-size: 22px;
            font-weight: 700;
            color: var(--color-primary);
            letter-spacing: 0.2px;
            line-height: 1.3;
        }

        .page-heading__subtitle {
            font-size: 14px;
            font-weight: 400;
            color: var(--color-text-light);
            margin-top: 8px;
        }

        /* ===== Buttons ===== */
        .btn-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            width: 100%;
        }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 280px;
            padding: 14px 24px;
            border-radius: 8px;
            font-family: var(--font-family);
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }

        .btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::after {
            left: 100%;
        }

        .btn--primary {
            background-color: var(--color-primary);
            color: var(--color-white);
            box-shadow: 0 4px 12px rgba(109, 26, 26, 0.35);
        }

        .btn--primary:hover {
            background-color: var(--color-primary-dark);
            box-shadow: 0 6px 18px rgba(109, 26, 26, 0.45);
            transform: translateY(-1px);
        }

        .btn--primary:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(109, 26, 26, 0.3);
        }

        .btn--secondary {
            background-color: var(--color-primary-light);
            color: var(--color-white);
            box-shadow: 0 4px 12px rgba(139, 32, 32, 0.3);
        }

        .btn--secondary:hover {
            background-color: var(--color-primary);
            box-shadow: 0 6px 18px rgba(109, 26, 26, 0.4);
            transform: translateY(-1px);
        }

        .btn--secondary:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(139, 32, 32, 0.25);
        }

        .btn--outline {
            background-color: transparent;
            color: var(--color-primary);
            border: 2px solid var(--color-primary);
            box-shadow: none;
        }

        .btn--outline:hover {
            background-color: var(--color-primary);
            color: var(--color-white);
            box-shadow: 0 4px 12px rgba(109, 26, 26, 0.3);
            transform: translateY(-1px);
        }

        .btn--outline:active {
            transform: translateY(0);
        }

        /* ===== Footer ===== */
        .page-footer {
            width: 100%;
            max-width: 420px;
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 24px;
            margin-top: auto;
        }

        .page-footer__logo {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .page-footer__logo-box {
            width: 40px;
            height: 40px;
            background-color: var(--color-primary);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .page-footer__logo-box svg {
            width: 24px;
            height: 24px;
            fill: var(--color-white);
        }

        .page-footer__logo-text {
            font-size: 9px;
            font-weight: 700;
            color: var(--color-primary);
            line-height: 1.2;
            text-transform: uppercase;
        }

        .page-footer__separator {
            width: 1px;
            height: 30px;
            background-color: var(--color-border);
        }

        .page-footer__brand {
            font-size: 14px;
            font-weight: 700;
            color: var(--color-primary-light);
            line-height: 1.2;
        }

        .page-footer__brand span {
            display: block;
            font-size: 11px;
            font-weight: 500;
            color: var(--color-text-light);
        }

        /* ===== Animation ===== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .animate-delay-1 { animation-delay: 0.1s; opacity: 0; }
        .animate-delay-2 { animation-delay: 0.2s; opacity: 0; }
        .animate-delay-3 { animation-delay: 0.3s; opacity: 0; }
        .animate-delay-4 { animation-delay: 0.4s; opacity: 0; }
        .animate-delay-5 { animation-delay: 0.5s; opacity: 0; }

        /* ===== Admin Dropdown Panel ===== */
        .top-bar__profile-wrapper {
            position: absolute;
            right: 16px;
            display: flex;
            align-items: center;
        }

        .top-bar__icon {
            position: relative;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s;
        }

        .top-bar__icon:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .admin-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: 300px;
            background: var(--color-white);
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.18);
            z-index: 2000;
            display: none;
            overflow: hidden;
            animation: dropdownFade 0.2s ease;
        }

        .admin-dropdown.show {
            display: block;
        }

        @keyframes dropdownFade {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .admin-dropdown__header {
            background: linear-gradient(135deg, var(--color-primary-light), var(--color-primary-dark));
            padding: 14px 16px;
            color: var(--color-white);
        }

        .admin-dropdown__header h3 {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .admin-dropdown__header p {
            font-size: 11px;
            opacity: 0.8;
        }

        .admin-list {
            display: flex;
            flex-direction: column;
            max-height: 260px;
            overflow-y: auto;
        }

        .admin-loading {
            text-align: center;
            color: var(--color-text-light);
            font-size: 13px;
            padding: 20px 0;
        }

        .admin-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            border-bottom: 1px solid var(--color-border);
            transition: background 0.15s;
        }

        .admin-card:last-child {
            border-bottom: none;
        }

        .admin-card:hover {
            background: #f9f5f5;
        }

        .admin-card__avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--color-primary-light), var(--color-primary-dark));
            color: var(--color-white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .admin-card__info {
            flex: 1;
            min-width: 0;
        }

        .admin-card__name {
            font-size: 13px;
            font-weight: 600;
            color: var(--color-text);
        }

        .admin-card__email {
            font-size: 11px;
            color: var(--color-text-light);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .admin-card__badge {
            font-size: 10px;
            font-weight: 600;
            background: var(--color-primary);
            color: var(--color-white);
            padding: 2px 7px;
            border-radius: 10px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .admin-empty {
            text-align: center;
            color: var(--color-text-light);
            font-size: 13px;
            padding: 20px 0;
        }

        /* ===== Responsive ===== */
        @media (min-width: 768px) {
            .main-content {
                max-width: 500px;
            }

            .page-heading__title {
                font-size: 26px;
            }

            .btn {
                max-width: 320px;
                padding: 16px 28px;
                font-size: 16px;
            }

            .admin-icon-area__shield {
                width: 140px;
                height: 140px;
        }

        /* ===== Custom Translate Select ===== */
        .custom-lang-selector {
            appearance: none;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: var(--color-white);
            border-radius: 6px;
            padding: 4px 24px 4px 10px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            outline: none;
            margin-right: 15px;
            background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23FFFFFF%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E");
            background-repeat: no-repeat;
            background-position: right 8px top 50%;
            background-size: 10px auto;
        }
        .custom-lang-selector option {
            color: #333;
            background: #fff;
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
    <!-- Top Bar -->
    <div class="top-bar" id="top-bar">
        <div class="top-bar__menu">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
            </svg>
        </div>
        <span class="top-bar__title">Circuit de Barcelona-Catalunya</span>
        <div class="top-bar__profile-wrapper">
            <select class="custom-lang-selector notranslate" id="custom-lang-selector">
                <option value="ca">CAT</option>
                <option value="es">ESP</option>
                <option value="en">ENG</option>
                <option value="fr">FRA</option>
            </select>
            <div class="top-bar__icon" id="btn-profile" title="Ver administradores">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
            </div>
            <div class="admin-dropdown" id="admin-dropdown">
                <div class="admin-dropdown__header">
                    <h3>🛡️ Administradores</h3>
                    <p>Usuarios con permisos de administración</p>
                </div>
                <div id="admin-list" class="admin-list">
                    <div class="admin-loading" id="admin-loading">Cargando...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Admin Icon -->
        <div class="admin-icon-area animate-fade-in animate-delay-1">
            <div class="admin-icon-area__shield">
                <svg viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
                    <!-- Shield shape -->
                    <defs>
                        <linearGradient id="shieldGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" style="stop-color:#8B2020;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#4A1010;stop-opacity:1" />
                        </linearGradient>
                    </defs>
                    <path d="M60 10 L105 30 L105 65 C105 85 85 105 60 115 C35 105 15 85 15 65 L15 30 Z" fill="url(#shieldGrad)" stroke="#3A0808" stroke-width="2"/>
                    <!-- Gear icon inside shield -->
                    <g transform="translate(60, 58)" fill="white">
                        <path d="M0-25 L5-22 L7-27 L10-27 L12-22 L17-25 L18-22 L14-18 L17-14 L22-12 L22-9 L17-7 L18-2 L22 0 L22 3 L17 5 L17 9 L22 12 L22 15 L17 14 L14 18 L18 22 L16 24 L12 20 L7 24 L7 27 L3 27 L3 22 L-3 22 L-3 27 L-7 27 L-7 24 L-12 20 L-16 24 L-18 22 L-14 18 L-17 14 L-22 15 L-22 12 L-17 9 L-17 5 L-22 3 L-22 0 L-17-2 L-18-7 L-22-9 L-22-12 L-17-14 L-14-18 L-18-22 L-16-24 L-12-20 L-7-24 L-7-27 L-3-27 L-3-22 L-5-22 Z" opacity="0.3" transform="scale(0.7)"/>
                        <!-- Settings/admin icon -->
                        <circle cx="0" cy="0" r="12" fill="none" stroke="white" stroke-width="2.5" opacity="0.9"/>
                        <circle cx="0" cy="0" r="4.5" fill="white" opacity="0.9"/>
                        <!-- Gear teeth -->
                        <rect x="-2" y="-17" width="4" height="7" rx="1.5" fill="white" opacity="0.9"/>
                        <rect x="-2" y="10" width="4" height="7" rx="1.5" fill="white" opacity="0.9"/>
                        <rect x="-17" y="-2" width="7" height="4" rx="1.5" fill="white" opacity="0.9"/>
                        <rect x="10" y="-2" width="7" height="4" rx="1.5" fill="white" opacity="0.9"/>
                        <!-- Diagonal teeth -->
                        <rect x="-2" y="-17" width="4" height="7" rx="1.5" fill="white" opacity="0.9" transform="rotate(45)"/>
                        <rect x="-2" y="10" width="4" height="7" rx="1.5" fill="white" opacity="0.9" transform="rotate(45)"/>
                        <rect x="-2" y="-17" width="4" height="7" rx="1.5" fill="white" opacity="0.9" transform="rotate(-45)"/>
                        <rect x="-2" y="10" width="4" height="7" rx="1.5" fill="white" opacity="0.9" transform="rotate(-45)"/>
                    </g>
                </svg>
            </div>
        </div>

        <!-- Page Title -->
        <div class="page-heading animate-fade-in animate-delay-2">
            <h1 class="page-heading__title">Panel de Administración</h1>
            <p class="page-heading__subtitle">Circuit de Barcelona-Catalunya</p>
        </div>

        <!-- Action Buttons -->
        <div class="btn-group">
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn btn--primary animate-fade-in animate-delay-3" id="btn-dashboard">
                        DASHBOARD
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn--primary animate-fade-in animate-delay-3" id="btn-login">
                        INICIAR SESIÓN
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn--secondary animate-fade-in animate-delay-4" id="btn-register">
                            REGISTRARSE
                        </a>
                    @endif
                @endauth
            @endif

            <a href="{{ route('map.show') }}" class="btn btn--outline animate-fade-in animate-delay-5" id="btn-map">
                🗺️ VER MAPA DE RUTAS
            </a>

            <a href="{{ route('map.editor') }}" class="btn btn--primary animate-fade-in animate-delay-5" id="btn-editor">
                ✏️ EDITOR DE CAMINOS
            </a>
        </div>


    </div>

    <!-- Footer -->
    <div class="page-footer animate-fade-in animate-delay-5">
        <div class="page-footer__logo">
            <div class="page-footer__logo-box">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <div class="page-footer__logo-text">
                Circuit de<br>Barcelona-<br>Catalunya
            </div>
        </div>
        <div class="page-footer__separator"></div>
        <div class="page-footer__brand">
            Metropolis<br>
            <span>FP Lab</span>
        </div>
    </div>
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

<script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.1/firebase-app.js";
    import { getFirestore, collection, getDocs } from "https://www.gstatic.com/firebasejs/10.8.1/firebase-firestore.js";

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

    // Toggle admin dropdown
    const btnProfile = document.getElementById('btn-profile');
    const adminDropdown = document.getElementById('admin-dropdown');

    btnProfile.addEventListener('click', (e) => {
        e.stopPropagation();
        adminDropdown.classList.toggle('show');
    });

    document.addEventListener('click', (e) => {
        if (!adminDropdown.contains(e.target) && e.target !== btnProfile) {
            adminDropdown.classList.remove('show');
        }
    });

    async function loadAdminUsers() {
        const adminList = document.getElementById('admin-list');
        const loading = document.getElementById('admin-loading');

        try {
            const querySnapshot = await getDocs(collection(db, "usuarios"));
            const admins = [];

            querySnapshot.forEach((docSnap) => {
                const data = docSnap.data();
                if (data.admin === true) {
                    admins.push({
                        id: docSnap.id,
                        nombre: data.nombre || 'Sin nombre',
                        apellidos: data.apellidos || '',
                        email: data.email || '',
                        telefono: data.telefono || ''
                    });
                }
            });

            loading.remove();

            if (admins.length === 0) {
                adminList.innerHTML = '<div class="admin-empty">No se encontraron administradores</div>';
                return;
            }

            admins.forEach(admin => {
                const initial = admin.nombre.charAt(0).toUpperCase();
                const fullName = admin.apellidos
                    ? `${admin.nombre} ${admin.apellidos}`
                    : admin.nombre;

                const card = document.createElement('div');
                card.className = 'admin-card';
                card.innerHTML = `
                    <div class="admin-card__avatar">${initial}</div>
                    <div class="admin-card__info">
                        <div class="admin-card__name">${fullName}</div>
                        <div class="admin-card__email">${admin.email || admin.telefono || 'Sin contacto'}</div>
                    </div>
                    <span class="admin-card__badge">ADMIN</span>
                `;
                adminList.appendChild(card);
            });

        } catch (e) {
            console.warn('Error loading admin users:', e);
            loading.textContent = 'Error al cargar administradores';
        }
    }

    loadAdminUsers();
</script>
</html>
