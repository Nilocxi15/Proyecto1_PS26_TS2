<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('icono-reciclaje.png') }}">
    <title>Gestión de Rutas</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
    </style>
</head>

<body>
    <div class="navbar-container">
        <nav class="navbar bg-body-tertiary fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="#"><img src="{{ asset('icono-reciclaje.png') }}" alt="Logo" width="30"
                        height="24" class="d-inline-block align-text-top"> Gestión de Rutas</a>
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
                                <a class="nav-link" href="{{ route('home-coordinator') }}"><i class="bi bi-house"></i>
                                    Inicio</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page"
                                    href="{{ route('coordinator.routes') }}"><i class="bi bi-signpost-split"></i>
                                    Gestión de Rutas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('coordinator.trucks') }}"><i class="bi bi-truck"></i>
                                    Gestión de Camiones</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('coordinator.collection-process') }}"><i
                                        class="bi bi-collection"></i> Proceso de Recolección</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('coordinator.incidents') }}"><i
                                        class="bi bi-exclamation-triangle"></i>
                                    Gestión de Denuncias Ciudadanas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('coordinator.profile') }}"><i
                                        class="bi bi-person-circle"></i> Mi perfil</a>
                            </li>
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
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </div>

    <div class="container mt-5 pt-4 pb-4">
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        @php
            $rutasCollection = $rutas ?? collect();
            $zonasCollection = $zonas ?? collect();
            $diasCollection = $diasSemana ?? collect();
        @endphp

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h1 class="h3 mb-0"><i class="bi bi-signpost-split"></i> Rutas registradas</h1>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createZoneModal">
                    <i class="bi bi-geo-alt"></i> Crear zona
                </button>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRouteModal">
                    <i class="bi bi-plus-circle"></i> Crear ruta
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Zona</th>
                        <th>Tipo residuo</th>
                        <th>Distancia (Km)</th>
                        <th>Horario</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rutasCollection as $ruta)
                        @php
                            $coordenadasRuta = $ruta->coordenadas
                                ->sortBy('orden')
                                ->values()
                                ->map(fn($coord) => [
                                    'lat' => (float) $coord->latitud,
                                    'lng' => (float) $coord->longitud,
                                ])
                                ->all();

                            $diasRuta = $ruta->dias
                                ->pluck('id_dia')
                                ->map(fn($id) => (int) $id)
                                ->values()
                                ->all();
                        @endphp
                        <tr>
                            <td>{{ $ruta->id_ruta }}</td>
                            <td>{{ $ruta->nombre }}</td>
                            <td>{{ $ruta->zona->nombre ?? ($ruta->id_zona ? 'Zona #' . $ruta->id_zona : 'Sin zona') }}</td>
                            <td>{{ $ruta->tipo_residuo ?? 'Sin definir' }}</td>
                            <td>{{ number_format((float) ($ruta->distancia_km ?? 0), 2) }}</td>
                            <td>{{ $ruta->horario_inicio ?? '--:--' }} - {{ $ruta->horario_fin ?? '--:--' }}</td>
                            <td>{{ $ruta->lat_inicio ?? '-' }}, {{ $ruta->lon_inicio ?? '-' }}</td>
                            <td>{{ $ruta->lat_fin ?? '-' }}, {{ $ruta->lon_fin ?? '-' }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                        data-bs-target="#editRouteModal" data-route-id="{{ $ruta->id_ruta }}"
                                        data-route-name="{{ $ruta->nombre }}" data-route-zone="{{ $ruta->id_zona }}"
                                        data-route-type="{{ $ruta->tipo_residuo }}"
                                        data-route-distance="{{ $ruta->distancia_km }}"
                                        data-route-start-time="{{ $ruta->horario_inicio }}"
                                        data-route-end-time="{{ $ruta->horario_fin }}"
                                        data-route-start-lat="{{ $ruta->lat_inicio }}"
                                        data-route-start-lng="{{ $ruta->lon_inicio }}"
                                        data-route-end-lat="{{ $ruta->lat_fin }}"
                                        data-route-end-lng="{{ $ruta->lon_fin }}"
                                        data-route-days='@json($diasRuta)'
                                        data-route-coords='@json($coordenadasRuta)'>
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    <form method="POST"
                                        action="{{ route('coordinator.routes.delete', ['ruta' => $ruta->id_ruta]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('¿Deseas eliminar esta ruta?');">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">No hay rutas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($rutasCollection instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="d-flex justify-content-center mt-3">
                {{ $rutasCollection->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif

        <section class="card mt-4">
            <div class="card-body">
                <h2 class="h5 mb-3"><i class="bi bi-map"></i> Mapa de rutas y paradas</h2>
                <div id="routesOverviewMap" style="height: 420px;" class="border rounded"></div>
                <small class="text-muted d-block mt-2">Las líneas muestran el trazado de cada ruta y los marcadores representan sus paradas.</small>
            </div>
        </section>
    </div>

    <div class="modal fade" id="createZoneModal" tabindex="-1" aria-labelledby="createZoneModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createZoneModalLabel">Crear zona</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('coordinator.zones.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre de zona</label>
                                <input type="text" class="form-control" name="nombre" maxlength="100" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo</label>
                                <select class="form-select" name="tipo" required>
                                    <option value="residencial">Residencial</option>
                                    <option value="comercial">Comercial</option>
                                    <option value="industrial">Industrial</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Latitud</label>
                                <input type="number" class="form-control" id="create_zone_latitud" name="latitud"
                                    step="0.0000001" min="-90" max="90" readonly required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Longitud</label>
                                <input type="number" class="form-control" id="create_zone_longitud" name="longitud"
                                    step="0.0000001" min="-180" max="180" readonly required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Ubicación de la zona en mapa</label>
                                <div id="createZoneMap" class="border rounded" style="height: 320px;"></div>
                                <div class="mt-2 d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="createZoneClearBtn">
                                        Limpiar ubicación
                                    </button>
                                </div>
                                <small class="text-muted">Haz clic en el mapa para seleccionar el punto central de la zona.</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar zona</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createRouteModal" tabindex="-1" aria-labelledby="createRouteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createRouteModalLabel">Crear ruta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('coordinator.routes.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Zona / colonia</label>
                                <select class="form-select" name="id_zona">
                                    <option value="">Sin zona</option>
                                    @foreach ($zonasCollection as $zona)
                                        <option value="{{ $zona->id_zona }}">{{ $zona->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo de residuo</label>
                                <select class="form-select" name="tipo_residuo" required>
                                    <option value="organico">Orgánico</option>
                                    <option value="inorganico">Inorgánico</option>
                                    <option value="mixto">Mixto</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Distancia (Km)</label>
                                <input type="number" class="form-control" id="create_distancia_km" name="distancia_km" min="0"
                                    step="0.01" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Horario inicio</label>
                                <input type="time" class="form-control" name="horario_inicio" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Horario fin</label>
                                <input type="time" class="form-control" name="horario_fin" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Latitud inicio</label>
                                <input type="number" class="form-control" id="create_lat_inicio" name="lat_inicio"
                                    step="0.0000001" min="-90" max="90" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Longitud inicio</label>
                                <input type="number" class="form-control" id="create_lon_inicio" name="lon_inicio"
                                    step="0.0000001" min="-180" max="180" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Latitud fin</label>
                                <input type="number" class="form-control" id="create_lat_fin" name="lat_fin"
                                    step="0.0000001" min="-90" max="90" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Longitud fin</label>
                                <input type="number" class="form-control" id="create_lon_fin" name="lon_fin"
                                    step="0.0000001" min="-180" max="180" readonly>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Días de recolección</label>
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach ($diasCollection as $dia)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="dias[]"
                                                value="{{ $dia->id_dia }}" id="create_day_{{ $dia->id_dia }}">
                                            <label class="form-check-label" for="create_day_{{ $dia->id_dia }}">
                                                {{ $dia->nombre }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Definición de ruta en mapa (clic para agregar paradas)</label>
                                <div id="createRouteMap" class="border rounded" style="height: 320px;"></div>
                                <div class="mt-2 d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="createClearRouteBtn">
                                        Limpiar trazado
                                    </button>
                                </div>
                                <input type="hidden" id="create_coordenadas_json" name="coordenadas_json" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear ruta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editRouteModal" tabindex="-1" aria-labelledby="editRouteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRouteModalLabel">Modificar ruta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="editRouteForm" action="#" data-base-action="{{ url('/coordinator/routes') }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Zona / colonia</label>
                                <select class="form-select" id="edit_id_zona" name="id_zona">
                                    <option value="">Sin zona</option>
                                    @foreach ($zonasCollection as $zona)
                                        <option value="{{ $zona->id_zona }}">{{ $zona->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo de residuo</label>
                                <select class="form-select" id="edit_tipo_residuo" name="tipo_residuo" required>
                                    <option value="organico">Orgánico</option>
                                    <option value="inorganico">Inorgánico</option>
                                    <option value="mixto">Mixto</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Distancia (Km)</label>
                                <input type="number" class="form-control" id="edit_distancia_km" name="distancia_km" min="0"
                                    step="0.01" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Horario inicio</label>
                                <input type="time" class="form-control" id="edit_horario_inicio" name="horario_inicio" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Horario fin</label>
                                <input type="time" class="form-control" id="edit_horario_fin" name="horario_fin" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Latitud inicio</label>
                                <input type="number" class="form-control" id="edit_lat_inicio" name="lat_inicio"
                                    step="0.0000001" min="-90" max="90" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Longitud inicio</label>
                                <input type="number" class="form-control" id="edit_lon_inicio" name="lon_inicio"
                                    step="0.0000001" min="-180" max="180" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Latitud fin</label>
                                <input type="number" class="form-control" id="edit_lat_fin" name="lat_fin"
                                    step="0.0000001" min="-90" max="90" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Longitud fin</label>
                                <input type="number" class="form-control" id="edit_lon_fin" name="lon_fin"
                                    step="0.0000001" min="-180" max="180" readonly>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Días de recolección</label>
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach ($diasCollection as $dia)
                                        <div class="form-check">
                                            <input class="form-check-input edit-day" type="checkbox" name="dias[]"
                                                value="{{ $dia->id_dia }}" id="edit_day_{{ $dia->id_dia }}">
                                            <label class="form-check-label" for="edit_day_{{ $dia->id_dia }}">
                                                {{ $dia->nombre }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Definición de ruta en mapa (clic para agregar paradas)</label>
                                <div id="editRouteMap" class="border rounded" style="height: 320px;"></div>
                                <div class="mt-2 d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="editClearRouteBtn">
                                        Limpiar trazado
                                    </button>
                                </div>
                                <input type="hidden" id="edit_coordenadas_json" name="coordenadas_json" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @php
        $rutasMapaCollection = $rutasMapa ?? collect();
        $routesMapData = collect(
            $rutasMapaCollection,
        )
            ->map(function ($ruta) {
                $coordenadas = $ruta->coordenadas
                    ->sortBy('orden')
                    ->values()
                    ->map(function ($coord) {
                        return [
                            'lat' => (float) $coord->latitud,
                            'lng' => (float) $coord->longitud,
                        ];
                    })
                    ->all();

                if (count($coordenadas) < 2 && $ruta->lat_inicio && $ruta->lon_inicio && $ruta->lat_fin && $ruta->lon_fin) {
                    $coordenadas = [
                        [
                            'lat' => (float) $ruta->lat_inicio,
                            'lng' => (float) $ruta->lon_inicio,
                        ],
                        [
                            'lat' => (float) $ruta->lat_fin,
                            'lng' => (float) $ruta->lon_fin,
                        ],
                    ];
                }

                return [
                    'id' => $ruta->id_ruta,
                    'nombre' => $ruta->nombre,
                    'coordenadas' => $coordenadas,
                ];
            })
            ->values()
            ->all();
    @endphp

    <script id="routesMapData" type="application/json">@json($routesMapData)</script>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="{{ asset('js/coordinator/routes.js') }}" defer></script>
</body>

</html>