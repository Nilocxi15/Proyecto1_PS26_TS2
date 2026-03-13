<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('icono-reciclaje.png') }}">
    <title>Gestión de Camiones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
</head>

<body>
    <div class="navbar-container">
        <nav class="navbar bg-body-tertiary fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="#"><img src="{{ asset('icono-reciclaje.png') }}" alt="Logo" width="30"
                        height="24" class="d-inline-block align-text-top"> Gestión de Camiones</a>
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
                                <a class="nav-link" href="{{ route('coordinator.routes') }}"><i
                                        class="bi bi-signpost-split"></i>
                                    Gestión de Rutas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page"
                                    href="{{ route('coordinator.trucks') }}"><i class="bi bi-truck"></i>
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

    @php
        $camionesCollection = $camiones ?? collect();
        $camionesCatalogoCollection = $camionesCatalogo ?? collect();
        $conductoresCollection = $conductores ?? collect();
        $rutasCollection = $rutas ?? collect();
        $programacionesCollection = $programaciones ?? collect();
        $assignmentMapData = $assignmentMapData ?? [];

        $filtersData = $filters ?? [
            'q' => '',
            'estado' => '',
            'id_conductor' => null,
            'prog_q' => '',
            'prog_ruta' => null,
            'prog_camion' => null,
        ];

        $allowedStatesData = $allowedStates ?? ['operativo', 'mantenimiento', 'fuera_servicio'];

        $stateLabel = [
            'operativo' => 'Operativo',
            'mantenimiento' => 'Mantenimiento',
            'fuera_servicio' => 'Fuera de servicio',
        ];

        $assignmentStateLabel = [
            'programada' => 'Programada',
        ];

        $stateBadge = [
            'operativo' => 'success',
            'mantenimiento' => 'warning text-dark',
            'fuera_servicio' => 'danger',
        ];
    @endphp

    <div class="container mt-5 pt-4 pb-4">
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h1 class="h3 mb-0"><i class="bi bi-truck"></i> Camiones registrados</h1>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assignRouteModal">
                    <i class="bi bi-diagram-3"></i> Asignar ruta
                </button>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTruckModal">
                    <i class="bi bi-plus-circle"></i> Agregar camión
                </button>
            </div>
        </div>

        <section class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('coordinator.trucks') }}" class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Búsqueda</label>
                        <input type="text" class="form-control" name="q" placeholder="Placa, capacidad, conductor..."
                            value="{{ $filtersData['q'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="">Todos</option>
                            @foreach ($allowedStatesData as $state)
                                <option value="{{ $state }}" @selected(($filtersData['estado'] ?? '') === $state)>
                                    {{ $stateLabel[$state] ?? ucfirst($state) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Conductor</label>
                        <select name="id_conductor" class="form-select">
                            <option value="">Todos</option>
                            @foreach ($conductoresCollection as $conductor)
                                <option value="{{ $conductor->id_usuario }}" @selected((int) ($filtersData['id_conductor'] ?? 0) === (int) $conductor->id_usuario)>
                                    {{ $conductor->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="submit" class="btn btn-outline-primary" title="Buscar">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <div class="col-12">
                        <a href="{{ route('coordinator.trucks') }}" class="btn btn-link p-0 text-decoration-none">Limpiar filtros</a>
                    </div>
                </form>
            </div>
        </section>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Placa</th>
                        <th>Capacidad (Ton)</th>
                        <th>Conductor actual</th>
                        <th>Estado</th>
                        <th style="min-width: 280px;">Actualizar conductor</th>
                        <th style="min-width: 220px;">Actualizar estado</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($camionesCollection as $camion)
                        <tr>
                            <td>{{ $camion->id_camion }}</td>
                            <td>{{ $camion->placa }}</td>
                            <td>{{ number_format((float) ($camion->capacidad_toneladas ?? 0), 2) }}</td>
                            <td>
                                @if ($camion->conductor)
                                    <div class="fw-semibold">{{ $camion->conductor->nombre }}</div>
                                    <small class="text-muted">{{ $camion->conductor->email }}</small>
                                @else
                                    <span class="text-muted">Sin asignar</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $estadoActual = (string) ($camion->estado ?? '');
                                    $badgeClass = $stateBadge[$estadoActual] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $badgeClass }}">
                                    {{ $stateLabel[$estadoActual] ?? ucfirst(str_replace('_', ' ', $estadoActual)) }}
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('coordinator.trucks.update-driver', ['camion' => $camion->id_camion]) }}" class="d-flex gap-2">
                                    @csrf
                                    @method('PUT')
                                    <select name="id_conductor" class="form-select form-select-sm">
                                        <option value="">Sin conductor</option>
                                        @foreach ($conductoresCollection as $conductor)
                                            <option value="{{ $conductor->id_usuario }}" @selected((int) $camion->id_conductor === (int) $conductor->id_usuario)>
                                                {{ $conductor->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Guardar conductor">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('coordinator.trucks.update-state', ['camion' => $camion->id_camion]) }}" class="d-flex gap-2">
                                    @csrf
                                    @method('PUT')
                                    <select name="estado" class="form-select form-select-sm">
                                        @foreach ($allowedStatesData as $state)
                                            <option value="{{ $state }}" @selected($camion->estado === $state)>
                                                {{ $stateLabel[$state] ?? ucfirst($state) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Guardar estado">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('coordinator.trucks.delete', ['camion' => $camion->id_camion]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('¿Deseas eliminar este camión?');"
                                        title="Eliminar camión">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No hay camiones registrados con los filtros actuales.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($camionesCollection instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="d-flex justify-content-center mt-3">
                {{ $camionesCollection->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif

        <section class="mt-5">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h2 class="h4 mb-0"><i class="bi bi-sign-turn-right"></i> Asignación de Camiones a Rutas</h2>
            </div>

            <section class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('coordinator.trucks') }}" class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label">Búsqueda</label>
                            <input type="text" class="form-control" name="prog_q" placeholder="Ruta, placa o fecha..."
                                value="{{ $filtersData['prog_q'] ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ruta</label>
                            <select name="prog_ruta" class="form-select">
                                <option value="">Todas</option>
                                @foreach ($rutasCollection as $ruta)
                                    <option value="{{ $ruta->id_ruta }}" @selected((int) ($filtersData['prog_ruta'] ?? 0) === (int) $ruta->id_ruta)>
                                        {{ $ruta->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Camión</label>
                            <select name="prog_camion" class="form-select">
                                <option value="">Todos</option>
                                @foreach ($camionesCatalogoCollection as $camionOption)
                                    <option value="{{ $camionOption->id_camion }}" @selected((int) ($filtersData['prog_camion'] ?? 0) === (int) $camionOption->id_camion)>
                                        {{ $camionOption->placa }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 d-grid">
                            <button type="submit" class="btn btn-outline-primary" title="Buscar asignaciones">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                        <div class="col-12">
                            <a href="{{ route('coordinator.trucks') }}" class="btn btn-link p-0 text-decoration-none">Limpiar filtros</a>
                        </div>
                    </form>
                </div>
            </section>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Ruta</th>
                            <th>Camión</th>
                            <th>Puntos</th>
                            <th>Total estimado (Kg)</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($programacionesCollection as $programacion)
                            <tr>
                                <td>{{ $programacion->id_programacion }}</td>
                                <td>{{ optional($programacion->fecha)->format('Y-m-d') ?? $programacion->fecha }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $programacion->ruta->nombre ?? 'Sin ruta' }}</div>
                                    <small class="text-muted">
                                        {{ optional($programacion->ruta->zona)->nombre ?? 'Sin zona' }}
                                    </small>
                                </td>
                                <td>{{ $programacion->camion->placa ?? 'Sin camión' }}</td>
                                <td>{{ (int) ($programacion->puntos_recoleccion_count ?? 0) }}</td>
                                <td>{{ number_format((float) ($programacion->total_basura_estimada_kg ?? 0), 2) }}</td>
                                <td>
                                    <span class="badge bg-info text-dark">
                                        {{ $assignmentStateLabel[$programacion->estado] ?? ucfirst($programacion->estado) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No hay rutas programadas con los filtros actuales.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($programacionesCollection instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="d-flex justify-content-center mt-3">
                    {{ $programacionesCollection->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            @endif

            <section class="card mt-4">
                <div class="card-body">
                    <h3 class="h5 mb-3"><i class="bi bi-map"></i> Puntos de recolección en rutas programadas</h3>
                    <div id="assignmentPointsMap" class="border rounded" style="height: 430px;"></div>
                    <small class="text-muted d-block mt-2">Los marcadores identifican los puntos estimados de recolección sobre las rutas programadas.</small>
                </div>
            </section>
        </section>
    </div>

    <div class="modal fade" id="createTruckModal" tabindex="-1" aria-labelledby="createTruckModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createTruckModalLabel">Agregar camión</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('coordinator.trucks.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Placa</label>
                                <input type="text" class="form-control" name="placa" maxlength="20" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Capacidad (Toneladas)</label>
                                <input type="number" class="form-control" name="capacidad_toneladas" min="0.01" step="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Estado</label>
                                <select class="form-select" name="estado" required>
                                    @foreach ($allowedStatesData as $state)
                                        <option value="{{ $state }}">{{ $stateLabel[$state] ?? ucfirst($state) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Conductor encargado</label>
                                <select class="form-select" name="id_conductor">
                                    <option value="">Sin conductor</option>
                                    @foreach ($conductoresCollection as $conductor)
                                        <option value="{{ $conductor->id_usuario }}">
                                            {{ $conductor->nombre }} ({{ $conductor->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar camión</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="assignRouteModal" tabindex="-1" aria-labelledby="assignRouteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignRouteModalLabel">Asignar camión a ruta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('coordinator.trucks.assign-route') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Ruta</label>
                                <select class="form-select" name="id_ruta" required>
                                    <option value="">Seleccionar ruta</option>
                                    @foreach ($rutasCollection as $ruta)
                                        <option value="{{ $ruta->id_ruta }}">
                                            {{ $ruta->nombre }}
                                            @if ($ruta->zona)
                                                - {{ $ruta->zona->nombre }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Camión</label>
                                <select class="form-select" name="id_camion" required>
                                    <option value="">Seleccionar camión</option>
                                    @foreach ($camionesCatalogoCollection as $camion)
                                        <option value="{{ $camion->id_camion }}">
                                            {{ $camion->placa }} - {{ number_format((float) $camion->capacidad_toneladas, 2) }} Ton
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha de programación</label>
                                <input type="date" class="form-control" name="fecha" value="{{ now()->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Estado inicial</label>
                                <input type="text" class="form-control" value="Programada" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Asignar ruta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script id="assignmentMapData" type="application/json">@json($assignmentMapData)</script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="{{ asset('js/coordinator/trucks.js') }}" defer></script>
</body>

</html>