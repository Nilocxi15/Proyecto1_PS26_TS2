<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('icono-reciclaje.png') }}">
    <title>Proceso de Recolección</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="{{ asset('js/coordinator/collectionProcess.js') }}" defer></script>
</head>

<body>
    <div class="navbar-container">
        <nav class="navbar bg-body-tertiary fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="#"><img src="{{ asset('icono-reciclaje.png') }}" alt="Logo" width="30"
                        height="24" class="d-inline-block align-text-top"> Proceso de Recolección</a>
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
                                <a class="nav-link" href="{{ route('coordinator.trucks') }}"><i class="bi bi-truck"></i>
                                    Gestión de Camiones</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page"
                                    href="{{ route('coordinator.collection-process') }}"><i
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

    @php
        $programacionesCollection = $programaciones ?? collect();
        $camionesCollection = $camiones ?? collect();
        $filtersData = $filters ?? [
            'q' => '',
            'estado' => '',
            'id_camion' => null,
            'fecha_desde' => '',
            'fecha_hasta' => '',
        ];

        $allowedStatesData = $allowedStates ?? ['programada', 'en_proceso', 'completada', 'incompleta'];

        $stateLabel = [
            'programada' => 'Programada',
            'en_proceso' => 'En proceso',
            'completada' => 'Completada',
            'incompleta' => 'Incompleta',
        ];

        $stateBadge = [
            'programada' => 'secondary',
            'en_proceso' => 'primary',
            'completada' => 'success',
            'incompleta' => 'danger',
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
            <h1 class="h3 mb-0"><i class="bi bi-collection"></i> Rutas Programadas</h1>
        </div>

        <section class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('coordinator.collection-process') }}" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Búsqueda</label>
                        <input type="text" class="form-control" name="q" placeholder="Ruta, camión, fecha u observaciones"
                            value="{{ $filtersData['q'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
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
                    <div class="col-md-2">
                        <label class="form-label">Camión</label>
                        <select name="id_camion" class="form-select">
                            <option value="">Todos</option>
                            @foreach ($camionesCollection as $camion)
                                <option value="{{ $camion->id_camion }}" @selected((int) ($filtersData['id_camion'] ?? 0) === (int) $camion->id_camion)>
                                    {{ $camion->placa }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Fecha desde</label>
                        <input type="date" name="fecha_desde" class="form-control" value="{{ $filtersData['fecha_desde'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Fecha hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control" value="{{ $filtersData['fecha_hasta'] ?? '' }}">
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        <a href="{{ route('coordinator.collection-process') }}" class="btn btn-outline-secondary">
                            Limpiar
                        </a>
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
                        <th>Estado</th>
                        <th>Hora inicio</th>
                        <th>Hora fin</th>
                        <th>Basura real (Ton)</th>
                        <th>Basura real (Kg puntos)</th>
                        <th>Observaciones</th>
                        <th>Incidencias</th>
                        <th style="min-width: 260px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($programacionesCollection as $programacion)
                        @php
                            $currentState = (string) ($programacion->estado ?? 'programada');
                            $startTime = $programacion->hora_inicio;
                            $endTime = $programacion->hora_fin;
                            $wasteTon = $programacion->basura_recolectada_ton;
                            $obs = trim((string) ($programacion->observaciones ?? ''));

                            $canSetStart = $startTime === null;
                            $canSetEnd = $endTime === null && $startTime !== null;
                            $canSetWaste = $wasteTon === null;
                            $canSetObs = $obs === '';

                            $stateOptions = match ($currentState) {
                                'programada' => ['programada', 'en_proceso'],
                                'en_proceso' => ['en_proceso', 'completada', 'incompleta'],
                                default => [$currentState],
                            };
                        @endphp
                        <tr>
                            <td>{{ $programacion->id_programacion }}</td>
                            <td>{{ optional($programacion->fecha)->format('Y-m-d') ?? $programacion->fecha }}</td>
                            <td>{{ $programacion->ruta->nombre ?? 'Sin ruta' }}</td>
                            <td>{{ $programacion->camion->placa ?? 'Sin camión' }}</td>
                            <td>
                                <span class="badge bg-{{ $stateBadge[$currentState] ?? 'secondary' }}">
                                    {{ $stateLabel[$currentState] ?? ucfirst($currentState) }}
                                </span>
                            </td>
                            <td>{{ $startTime ? \Carbon\Carbon::parse($startTime)->format('Y-m-d H:i') : 'Sin registrar' }}</td>
                            <td>{{ $endTime ? \Carbon\Carbon::parse($endTime)->format('Y-m-d H:i') : 'Sin registrar' }}</td>
                            <td>{{ $wasteTon !== null ? number_format((float) $wasteTon, 2) : 'Sin registrar' }}</td>
                            <td>{{ number_format((float) ($programacion->total_basura_real_kg ?? 0), 2) }}</td>
                            <td>{{ $obs !== '' ? $obs : 'Sin registrar' }}</td>
                            <td>{{ (int) ($programacion->incidencias_count ?? 0) }}</td>
                            <td>
                                <div class="d-flex flex-column gap-2">
                                    <form method="POST" action="{{ route('coordinator.collection-process.update-state', ['programacion' => $programacion->id_programacion]) }}" class="d-flex gap-2">
                                        @csrf
                                        @method('PUT')
                                        <select name="estado" class="form-select form-select-sm">
                                            @foreach ($stateOptions as $stateOption)
                                                <option value="{{ $stateOption }}" @selected($currentState === $stateOption)>
                                                    {{ $stateLabel[$stateOption] ?? ucfirst($stateOption) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Guardar estado">
                                            <i class="bi bi-check2"></i>
                                        </button>
                                    </form>

                                    <div class="d-flex gap-2">
                                        <form method="POST" action="{{ route('coordinator.collection-process.set-start-time', ['programacion' => $programacion->id_programacion]) }}">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-outline-success" @disabled(!$canSetStart)>
                                                Inicio
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('coordinator.collection-process.set-end-time', ['programacion' => $programacion->id_programacion]) }}">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" @disabled(!$canSetEnd)>
                                                Fin
                                            </button>
                                        </form>
                                    </div>

                                    <form method="POST" action="{{ route('coordinator.collection-process.set-waste', ['programacion' => $programacion->id_programacion]) }}" class="d-flex gap-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" step="0.01" min="0.01" name="basura_recolectada_ton"
                                            class="form-control form-control-sm" placeholder="Ton"
                                            @disabled(!$canSetWaste)>
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" @disabled(!$canSetWaste)>
                                            Basura
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('coordinator.collection-process.set-observations', ['programacion' => $programacion->id_programacion]) }}" class="d-flex gap-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="observaciones" class="form-control form-control-sm"
                                            placeholder="Observaciones" @disabled(!$canSetObs)>
                                        <button type="submit" class="btn btn-sm btn-outline-info" @disabled(!$canSetObs)>
                                            Obs
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-sm btn-outline-dark"
                                        data-bs-toggle="modal"
                                        data-bs-target="#incidentModal"
                                        data-incident-route="{{ $programacion->ruta->nombre ?? 'Ruta' }}"
                                        data-incident-action="{{ route('coordinator.collection-process.store-incident', ['programacion' => $programacion->id_programacion]) }}">
                                        Registrar incidencia
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted">No hay rutas programadas para mostrar.</td>
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

        @php
            $incidenciasCollection = $incidencias ?? collect();
            $incFilters = $incidenciasFilters ?? ['inc_q' => '', 'inc_id_prog' => null, 'inc_fecha_desde' => '', 'inc_fecha_hasta' => ''];
            $progList = $programacionesList ?? collect();
        @endphp

        <hr class="my-5">

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h2 class="h4 mb-0"><i class="bi bi-exclamation-triangle"></i> Incidencias de Recolección</h2>
        </div>

        <section class="card mb-3" id="incidencias">
            <div class="card-body">
                <form method="GET" action="{{ route('coordinator.collection-process') }}" class="row g-2 align-items-end">
                    {{-- Preserve main table filters --}}
                    <input type="hidden" name="q" value="{{ $filtersData['q'] ?? '' }}">
                    <input type="hidden" name="estado" value="{{ $filtersData['estado'] ?? '' }}">
                    <input type="hidden" name="id_camion" value="{{ $filtersData['id_camion'] ?? '' }}">
                    <input type="hidden" name="fecha_desde" value="{{ $filtersData['fecha_desde'] ?? '' }}">
                    <input type="hidden" name="fecha_hasta" value="{{ $filtersData['fecha_hasta'] ?? '' }}">
                    <div class="col-md-4">
                        <label class="form-label">Búsqueda</label>
                        <input type="text" class="form-control" name="inc_q" placeholder="Descripción de la incidencia"
                            value="{{ $incFilters['inc_q'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Programación</label>
                        <select name="inc_id_prog" class="form-select">
                            <option value="">Todas</option>
                            @foreach ($progList as $prog)
                                <option value="{{ $prog->id_programacion }}"
                                    @selected((int) ($incFilters['inc_id_prog'] ?? 0) === (int) $prog->id_programacion)>
                                    #{{ $prog->id_programacion }} – {{ $prog->ruta->nombre ?? 'Sin ruta' }}
                                    ({{ optional($prog->fecha)->format('Y-m-d') ?? $prog->fecha }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Fecha desde</label>
                        <input type="date" name="inc_fecha_desde" class="form-control" value="{{ $incFilters['inc_fecha_desde'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha hasta</label>
                        <input type="date" name="inc_fecha_hasta" class="form-control" value="{{ $incFilters['inc_fecha_hasta'] ?? '' }}">
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        <a href="{{ route('coordinator.collection-process') }}#incidencias" class="btn btn-outline-secondary">
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </section>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Prog. ID</th>
                        <th>Ruta</th>
                        <th>Camión</th>
                        <th>Fecha</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($incidenciasCollection as $incidencia)
                        <tr>
                            <td>{{ $incidencia->id_incidencia }}</td>
                            <td>{{ $incidencia->id_programacion }}</td>
                            <td>{{ $incidencia->programacion->ruta->nombre ?? 'Sin ruta' }}</td>
                            <td>{{ $incidencia->programacion->camion->placa ?? 'Sin camión' }}</td>
                            <td>{{ $incidencia->fecha ? \Carbon\Carbon::parse($incidencia->fecha)->format('Y-m-d H:i') : 'Sin fecha' }}</td>
                            <td>{{ $incidencia->descripcion }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No hay incidencias para mostrar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($incidenciasCollection instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="d-flex justify-content-center mt-3">
                {{ $incidenciasCollection->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    <div class="modal fade" id="incidentModal" tabindex="-1" aria-labelledby="incidentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="incidentModalLabel">Registrar incidencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="incidentForm" action="#">
                    @csrf
                    <div class="modal-body">
                        <p class="mb-2"><strong>Ruta:</strong> <span id="incidentRouteLabel">--</span></p>
                        <div>
                            <label class="form-label">Descripción de incidencia</label>
                            <textarea class="form-control" name="descripcion" rows="4" maxlength="1000" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar incidencia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


</body>

</html>