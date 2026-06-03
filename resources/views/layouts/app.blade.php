<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2c3e50">
    <title>Santa Cruz Segura Predictiva</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @stack('styles')
    
    <style>
        :root {
            --scsp-font-scale: 1;
            --scsp-bg: #f8f9fa;
            --scsp-text: #212529;
        }

        body.accessibility-mode {
            --scsp-font-scale: 1.25;
            --scsp-bg: #000;
            --scsp-text: #fff;
            background-color: var(--scsp-bg) !important;
            color: var(--scsp-text) !important;
        }

        body.accessibility-mode .card-mobile,
        body.accessibility-mode .bottom-nav,
        body.accessibility-mode .selector-barrio {
            background: #111 !important;
            color: #fff !important;
            border: 2px solid #fff;
        }

        body.accessibility-mode .btn {
            min-height: 52px;
            font-size: calc(18px * var(--scsp-font-scale));
        }

        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background-color: var(--scsp-bg);
            color: var(--scsp-text);
            font-size: calc(16px * var(--scsp-font-scale));
            padding-bottom: 70px;
        }

        .skip-link {
            position: absolute;
            top: -100px;
            left: 0;
            background: #000;
            color: #fff;
            padding: 12px 20px;
            z-index: 2000;
        }
        .skip-link:focus { top: 0; }
        
        /* ============================================ */
        /* ESTILOS MOBILE-FIRST (para celulares) */
        /* ============================================ */
        
        /* Botones grandes para dedos */
        .btn-mobile {
            padding: 14px 20px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 12px;
            margin: 5px 0;
        }
        
        /* Tarjetas con sombra suave */
        .card-mobile {
            border-radius: 16px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 16px;
            transition: transform 0.2s;
        }
        
        .card-mobile:active {
            transform: scale(0.98);
        }
        
        /* Estadísticas en cards grandes */
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 20px;
            color: white;
            margin-bottom: 16px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            margin: 10px 0;
        }
        
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
        
        /* Navegación inferior tipo móvil */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .bottom-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #6c757d;
            font-size: 12px;
            padding: 8px;
            border-radius: 12px;
            transition: all 0.2s;
        }
        
        .bottom-nav-item i {
            font-size: 24px;
            margin-bottom: 4px;
        }
        
        .bottom-nav-item.active {
            color: #3498db;
            background-color: #e3f2fd;
        }
        
        .bottom-nav-item:active {
            transform: scale(0.95);
        }
        
        /* Sidebar oculto en móvil (menú hamburguesa) */
        .sidebar-mobile {
            position: fixed;
            top: 0;
            left: -280px;
            width: 280px;
            height: 100%;
            background: #2c3e50;
            color: white;
            transition: left 0.3s ease;
            z-index: 1050;
            overflow-y: auto;
        }
        
        .sidebar-mobile.open {
            left: 0;
        }
        
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
            display: none;
        }
        
        .sidebar-overlay.open {
            display: block;
        }
        
        /* Header móvil */
        .mobile-header {
            background: #2c3e50;
            color: white;
            padding: 15px;
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .menu-btn {
            background: none;
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            padding: 8px;
        }
        
        /* Formularios optimizados para móvil */
        .form-mobile input,
        .form-mobile select,
        .form-mobile textarea {
            font-size: 16px; /* Evita zoom en iOS */
            padding: 14px;
            border-radius: 12px;
            border: 1px solid #dee2e6;
            margin-bottom: 15px;
        }
        
        .btn-reportar {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 16px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 50px;
            width: 100%;
            margin-top: 20px;
        }
        
        /* Ocultar sidebar desktop en móvil */
        .sidebar-desktop {
            display: none;
        }
        
        /* Estilos para tablet (pantallas >= 768px) */
        @media (min-width: 768px) {
            body {
                padding-bottom: 0;
            }
            
            .mobile-header {
                display: none;
            }
            
            .bottom-nav {
                display: none;
            }
            
            .sidebar-desktop {
                display: block;
                position: fixed;
                top: 0;
                left: 0;
                width: 260px;
                height: 100%;
                background: #2c3e50;
                color: white;
                overflow-y: auto;
            }
            
            .main-content-desktop {
                margin-left: 260px;
                padding: 20px;
            }
            
            .stat-number {
                font-size: 28px;
            }
        }
        
        /* Estilos para botón flotante de reporte rápido */
        .fab-report {
            position: fixed;
            bottom: 80px;
            right: 20px;
            width: 56px;
            height: 56px;
            border-radius: 28px;
            background: #e74c3c;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            cursor: pointer;
            z-index: 100;
            border: none;
            font-size: 24px;
        }
        
        .fab-report:active {
            transform: scale(0.95);
        }
        
        @media (min-width: 768px) {
            .fab-report {
                bottom: 30px;
                right: 30px;
            }
        }
        
        /* Loading spinner */
        .loading {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2000;
            display: none;
        }
        
        /* Alertas toast (notificaciones emergentes) */
        .toast-mobile {
            position: fixed;
            bottom: 80px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 12px 20px;
            border-radius: 50px;
            font-size: 14px;
            z-index: 1100;
            display: none;
            white-space: nowrap;
        }
    </style>
</head>
<body>
<a href="#main-content" class="skip-link">Saltar al contenido principal</a>

<!-- Barra accesibilidad -->
<div class="d-flex justify-content-end gap-2 px-2 py-1 bg-dark text-white small" role="toolbar" aria-label="Opciones de accesibilidad">
    <button type="button" class="btn btn-sm btn-outline-light" id="toggleAccessibility" aria-pressed="false" title="Alto contraste y texto ampliado">
        <i class="bi bi-universal-access"></i> Accesible
    </button>
</div>

<!-- Loading -->
<div class="loading" id="loading">
    <div class="spinner-border text-danger" role="status">
        <span class="visually-hidden">Cargando...</span>
    </div>
</div>

<!-- Toast para notificaciones -->
<div class="toast-mobile" id="toastMessage"></div>

<!-- ============================================ -->
<!-- MENÚ LATERAL MÓVIL (Hamburguesa) -->
<!-- ============================================ -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="sidebar-mobile" id="sidebarMobile">
    <div class="p-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">📱 Menú</h5>
            <button class="btn-close btn-close-white" id="closeMenuBtn"></button>
        </div>
        <hr class="bg-secondary">
        <nav class="nav flex-column">
            <a class="nav-link text-white py-3" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
            <a class="nav-link text-white py-3" href="{{ route('dashboard', ['barrio_id' => 'todos']) }}#map">
                <i class="bi bi-map me-2"></i> Mapa (todos)
            </a>
            <a class="nav-link text-white py-3" href="{{ route('incidentes.create') }}">
                <i class="bi bi-plus-circle me-2"></i> Nuevo Reporte
            </a>
            <a class="nav-link text-white py-3" href="{{ route('incidentes.index', ['alcance' => 'todos']) }}">
                <i class="bi bi-clock-history me-2"></i> Todos los incidentes
            </a>
            @if(auth()->user()?->puedeAdministrar())
            <a class="nav-link text-white py-3" href="{{ route('admin.incidentes.index') }}">
                <i class="bi bi-shield-lock me-2"></i> Admin incidentes
            </a>
            @endif
            @if(auth()->user()?->puedeValidar())
            <a class="nav-link text-white py-3" href="{{ route('reportes.mensual') }}">
                <i class="bi bi-graph-up me-2"></i> Estadísticas
            </a>
            @endif
            <a class="nav-link text-white py-3" href="#">
                <i class="bi bi-robot me-2"></i> Análisis IA
            </a>
        </nav>
        <hr class="bg-secondary">
        <div class="mt-3 small text-white-50">
            <i class="bi bi-person-circle me-1"></i> SuperAdmin<br>
            <i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- HEADER MÓVIL -->
<!-- ============================================ -->
<div class="mobile-header d-flex justify-content-between align-items-center">
    <button class="menu-btn" id="menuBtn">
        <i class="bi bi-list"></i>
    </button>
    <div class="text-center flex-grow-1">
        <strong>Santa Cruz Segura</strong>
    </div>
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-bell fs-5"></i>
        @auth
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-link text-white p-0"><i class="bi bi-box-arrow-right fs-5"></i></button>
            </form>
        @endauth
    </div>
</div>

<!-- ============================================ -->
<!-- SIDEBAR DESKTOP -->
<!-- ============================================ -->
<div class="sidebar-desktop">
    <div class="p-3">
        <h4 class="mb-0">Santa Cruz</h4>
        <h6 class="text-white-50">Segura Predictiva</h6>
    </div>
    <hr class="bg-secondary">
    <nav class="nav flex-column">
        <a class="nav-link text-white py-2" href="{{ route('dashboard') }}">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>
        <a class="nav-link text-white py-2" href="{{ route('dashboard') }}#map">
            <i class="bi bi-map me-2"></i> Mapa Predictivo
        </a>
        <a class="nav-link text-white py-2" href="{{ route('incidentes.create') }}">
            <i class="bi bi-plus-circle me-2"></i> Nuevo Registro
        </a>
        <a class="nav-link text-white py-2" href="{{ route('incidentes.index', ['alcance' => 'mi_barrio']) }}">
            <i class="bi bi-house me-2"></i> Mi barrio
        </a>
        <a class="nav-link text-white py-2" href="{{ route('incidentes.index', ['alcance' => 'todos']) }}">
            <i class="bi bi-globe-americas me-2"></i> Todos los incidentes
        </a>
        <a class="nav-link text-white py-2" href="{{ route('dashboard', ['barrio_id' => 'todos']) }}#map">
            <i class="bi bi-map me-2"></i> Mapa (todos)
        </a>
        @auth
        @if(auth()->user()->puedeValidar())
        <a class="nav-link text-white py-2" href="{{ route('reportes.mensual') }}">
            <i class="bi bi-graph-up me-2"></i> Estadísticas / PDF
        </a>
        @endif
        @if(auth()->user()->puedeAdministrar())
        <a class="nav-link text-white py-2" href="{{ route('admin.incidentes.index') }}">
            <i class="bi bi-shield-lock me-2"></i> Admin incidentes
        </a>
        @endif
        <a class="nav-link text-white py-2" href="{{ route('alertas.index') }}">
            <i class="bi bi-bell me-2"></i> Alertas
        </a>
        @endauth
        @auth
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="nav-link text-white py-2 btn btn-link text-start">
                    <i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión
                </button>
            </form>
        @endauth
    </nav>
    <hr class="bg-secondary">
    <div class="p-3">
        @auth
        <small><i class="bi bi-person-circle"></i> {{ auth()->user()->name }}</small><br>
        <small class="text-white-50">{{ auth()->user()->nombreRol() }}</small>
        @else
        <small class="text-white-50">Invitado</small>
        @endauth
    </div>
</div>

<!-- ============================================ -->
<!-- CONTENIDO PRINCIPAL -->
<!-- ============================================ -->
<div class="main-content-desktop" id="main-content">
    <div class="container-fluid px-2 px-md-4">
        <div class="pt-2 pt-md-3 pb-2">
            <h2 class="h3">@yield('title', 'INICIO')</h2>
        </div>
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-x-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i> {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-x-circle me-2"></i> Por favor corrige los errores
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @yield('content')
    </div>
</div>

<!-- ============================================ -->
<!-- BOTÓN FLOTANTE PARA REPORTE RÁPIDO -->
<!-- ============================================ -->
<a href="{{ route('incidentes.create') }}" class="fab-report">
    <i class="bi bi-exclamation-triangle-fill"></i>
</a>

<!-- ============================================ -->
<!-- NAVEGACIÓN INFERIOR (MÓVIL) -->
<!-- ============================================ -->
<div class="bottom-nav">
    <a href="{{ route('dashboard') }}" class="bottom-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="bi bi-house-door-fill"></i>
        <span>Inicio</span>
    </a>
    <a href="{{ route('dashboard', ['barrio_id' => 'todos']) }}#map" class="bottom-nav-item">
        <i class="bi bi-map-fill"></i>
        <span>Mapa</span>
    </a>
    <a href="{{ route('incidentes.index', ['alcance' => 'todos']) }}" class="bottom-nav-item {{ request()->routeIs('incidentes.index') ? 'active' : '' }}">
        <i class="bi bi-list-ul"></i>
        <span>Incidentes</span>
    </a>
    <a href="{{ route('incidentes.create') }}" class="bottom-nav-item">
        <i class="bi bi-plus-circle-fill text-danger"></i>
        <span>Reportar</span>
    </a>
    <a href="{{ route('alertas.index') }}" class="bottom-nav-item {{ request()->routeIs('alertas.*') ? 'active' : '' }}">
        <i class="bi bi-bell-fill"></i>
        <span>Alertas</span>
    </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Accesibilidad: alto contraste + texto ampliado
    const toggleA11y = document.getElementById('toggleAccessibility');
    if (toggleA11y) {
        const saved = localStorage.getItem('scsp_a11y') === '1';
        if (saved) {
            document.body.classList.add('accessibility-mode');
            toggleA11y.setAttribute('aria-pressed', 'true');
        }
        toggleA11y.addEventListener('click', () => {
            document.body.classList.toggle('accessibility-mode');
            const on = document.body.classList.contains('accessibility-mode');
            toggleA11y.setAttribute('aria-pressed', on ? 'true' : 'false');
            localStorage.setItem('scsp_a11y', on ? '1' : '0');
        });
    }

    // Menú hamburguesa móvil
    const menuBtn = document.getElementById('menuBtn');
    const sidebarMobile = document.getElementById('sidebarMobile');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const closeMenuBtn = document.getElementById('closeMenuBtn');
    
    function openMenu() {
        sidebarMobile.classList.add('open');
        sidebarOverlay.classList.add('open');
    }
    
    function closeMenu() {
        sidebarMobile.classList.remove('open');
        sidebarOverlay.classList.remove('open');
    }
    
    if (menuBtn) menuBtn.addEventListener('click', openMenu);
    if (closeMenuBtn) closeMenuBtn.addEventListener('click', closeMenu);
    if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeMenu);
    
    // Función para mostrar toast
    function showToast(message, duration = 3000) {
        const toast = document.getElementById('toastMessage');
        toast.textContent = message;
        toast.style.display = 'block';
        setTimeout(() => {
            toast.style.display = 'none';
        }, duration);
    }
    
    // Función para mostrar loading
    function showLoading() {
        document.getElementById('loading').style.display = 'flex';
    }
    
    function hideLoading() {
        document.getElementById('loading').style.display = 'none';
    }
    
    // Geolocalización automática (si está disponible)
    function getCurrentLocation() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject('Geolocalización no soportada');
                return;
            }
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    resolve({
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    });
                },
                (error) => {
                    reject('Error al obtener ubicación: ' + error.message);
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        });
    }
</script>
@stack('scripts')
</body>
</html>