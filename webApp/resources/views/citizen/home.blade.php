<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('icono-reciclaje.png') }}">
    <link rel="stylesheet" href="{{ asset('css/citizen/home.css') }}">
    <title>Inicio</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
</head>

<body>
    @php
        $estaAutenticado = auth()->check();
        $usuario = auth()->user();
        $esOperadorPuntoVerde = $estaAutenticado && (int) ($usuario?->id_role ?? 0) === 3;
        $esCiudadano = $estaAutenticado && (int) ($usuario?->id_role ?? 0) === 4;
    @endphp

    <div class="navbar-container">
        <nav class="navbar bg-body-tertiary fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="#"><img src="{{ asset('icono-reciclaje.png') }}" alt="Logo" width="30"
                        height="24" class="d-inline-block align-text-top"> EcoGestor Ciudadano</a>
                @if (!$estaAutenticado)
                    <a class="btn btn-success btn-sm d-none d-lg-inline-flex align-items-center me-2"
                        href="{{ route('login') }}">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar sesión
                    </a>
                @endif
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar"
                    aria-labelledby="offcanvasNavbarLabel">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menú</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="{{ route('home-public') }}"><i
                                        class="bi bi-house"></i>
                                    Inicio</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#rutas"><i class="bi bi-signpost"></i> Horarios y rutas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#puntos-verdes"><i class="bi bi-geo-alt"></i> Puntos verdes
                                    cercanos</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('citizen.public-statistics') }}"><i
                                        class="bi bi-bar-chart"></i> Estadísticas públicas</a>
                            </li>
                            @if ($estaAutenticado)
                                @if ($esCiudadano)
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('report-citizen') }}"><i
                                                class="bi bi-file-earmark-medical"></i> Reportes y denuncias</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('profile-citizen') }}"><i
                                                class="bi bi-person-circle"></i> Mi perfil</a>
                                    </li>
                                @endif
                                @if ($esOperadorPuntoVerde)
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('operator.containers') }}"><i
                                                class="bi bi-trash"></i> Gestión de contenedores</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('profile-operator') }}"><i
                                                class="bi bi-person-circle"></i> Mi perfil</a>
                                    </li>
                                @endif
                                @if(auth()->check() && (int) auth()->user()->id_role === 1)
                                <li class="nav-item">
                                    <a class="nav-link fw-semibold" href="{{ route('home-admin') }}"><i class="bi bi-shield-fill"></i> Panel de Administrador</a>
                                </li>
                                @endif
                                <li class="nav-item">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
                                            <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                                        </button>
                                    </form>
                                </li>
                            @else
                                <li class="nav-item mt-2">
                                    <a class="btn btn-success w-100" href="{{ route('login') }}">
                                        <i class="bi bi-box-arrow-in-right"></i> Iniciar sesión
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </div>

    <div class="body-content">
        <h2 id="rutas"><i class="bi bi-calendar-event"></i> Consulta de horarios y rutas de recolección</h2>
        <section class="schedule-tools mb-3">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg-6">
                    <label for="searchInput" class="form-label mb-1">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="searchInput" class="form-control"
                            placeholder="Buscar por ruta, residuo u horario...">
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <label for="dayFilter" class="form-label mb-1">Filtrar por día</label>
                    <select id="dayFilter" class="form-select">
                        <option value="">Todos los días</option>
                        <option value="Lunes">Lunes</option>
                        <option value="Martes">Martes</option>
                        <option value="Miércoles">Miércoles</option>
                        <option value="Jueves">Jueves</option>
                        <option value="Viernes">Viernes</option>
                        <option value="Sábado">Sábado</option>
                        <option value="Domingo">Domingo</option>
                    </select>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <label for="wasteTypeFilter" class="form-label mb-1">Filtrar por residuo</label>
                    <select id="wasteTypeFilter" class="form-select">
                        <option value="">Todos los tipos</option>
                        <option value="Organico">Orgánico</option>
                        <option value="Mixto">Mixto</option>
                        <option value="Inorganico">Inorgánico</option>
                    </select>
                </div>
            </div>
        </section>

        <div class="table-responsive">
            <table class="table table-striped table-hover" id="scheduleTable">
                <thead class="table-dark">
                    <tr>
                        <th><i class="bi bi-geo-alt"></i> Ruta</th>
                        <th><i class="bi bi-recycle"></i> Tipo de Residuo</th>
                        <th><i class="bi bi-clock-history"></i> Hora Inicio</th>
                        <th><i class="bi bi-clock"></i> Hora Fin</th>
                        <th><i class="bi bi-calendar-week"></i> Día</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (($horarios ?? collect()) as $horario)
                        <tr>
                            <td>{{ $horario['ruta'] ?? 'Ruta sin nombre' }}</td>
                            <td>{{ $horario['tipo_residuo'] ?? 'Sin definir' }}</td>
                            <td>{{ $horario['hora_inicio'] ?? '--:--' }}</td>
                            <td>{{ $horario['hora_fin'] ?? '--:--' }}</td>
                            <td>{{ $horario['dia'] ?? 'Sin definir' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p id="emptyState" class="empty-state d-none mb-0">No hay rutas que coincidan con los filtros aplicados.</p>

            <div id="tablePagination" class="table-pagination mt-3" aria-label="Paginación de horarios">
                <button type="button" id="prevPageBtn" class="btn btn-outline-secondary btn-sm">Anterior</button>
                <span id="pageInfo" class="page-info">Página 1 de 1</span>
                <button type="button" id="nextPageBtn" class="btn btn-outline-secondary btn-sm">Siguiente</button>
            </div>

            <div class="map-tools mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-geo"></i></span>
                    <input type="text" id="mapAddressInput" class="form-control"
                        placeholder="Buscar direccion o zona...">
                    <button type="button" id="mapSearchBtn" class="btn btn-primary">Buscar</button>
                    <button type="button" id="myLocationBtn" class="btn btn-outline-primary">Mi ubicación</button>
                </div>
                <small id="mapSearchMessage" class="text-muted d-block mt-2"></small>
            </div>

            <div class="map-wrapper">
                <div id="routesMap"></div>
            </div>
        </div>

        <section id="puntos-verdes" class="mt-4">
            <h2><i class="bi bi-tree"></i> Consulta de puntos verdes cercanos</h2>
            <p class="mb-2">Mapa de rutas de recoleccion y puntos de parada estimados.</p>

            <div class="map-tools mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-geo"></i></span>
                    <input type="text" id="greenMapAddressInput" class="form-control"
                        placeholder="Buscar direccion para puntos verdes...">
                    <button type="button" id="greenMapSearchBtn" class="btn btn-primary">Buscar</button>
                    <button type="button" id="greenMyLocationBtn" class="btn btn-outline-primary">Mi ubicación</button>
                </div>
                <small id="greenMapSearchMessage" class="text-muted d-block mt-2"></small>
            </div>

            <div class="map-wrapper">
                <div id="greenPointsMap"></div>
            </div>
        </section>

        <section class="mt-4">
            <h2><i class="bi bi-bar-chart"></i> Visualización de estadísticas públicas</h2>
            <p>
                <a class="btn btn-outline-primary" href="{{ route('citizen.public-statistics') }}">
                    Ver estadísticas públicas
                </a>
            </p>
        </section>

        @if ($esCiudadano)
            <section class="mt-4">
                <h2><i class="bi bi-file-earmark-medical"></i> Gestión de denuncias ciudadanas</h2>
                <p>Desde reportes puedes crear denuncias de basureros clandestinos y dar seguimiento a tus reportes.</p>
                <a class="btn btn-outline-success" href="{{ route('report-citizen') }}">Ir a reportes y seguimiento</a>
            </section>
        @endif

        @if ($esOperadorPuntoVerde)
            <section class="mt-4">
                <h2><i class="bi bi-trash"></i> Gestión operativa de puntos verdes</h2>
                <p>Desde gestión de contenedores puedes revisar niveles de llenado y registrar solicitudes de vaciado.</p>
                <a class="btn btn-outline-success" href="{{ route('operator.containers') }}">Ir a gestión de contenedores</a>
            </section>
        @endif
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        window.citizenRoutes = @json($mapRoutes ?? []);
        window.greenPoints = @json($greenPoints ?? []);
    </script>
    <script src="{{ asset('js/citizen/home.js') }}"></script>

</body>

</html>