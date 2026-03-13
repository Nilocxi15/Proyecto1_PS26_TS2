<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('icono-reciclaje.png') }}">
    <title>Gestión de Denuncias Ciudadanas</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
</head>

<body>
    <div class="navbar-container">
        <nav class="navbar bg-body-tertiary fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="#"><img src="{{ asset('icono-reciclaje.png') }}" alt="Logo" width="30"
                        height="24" class="d-inline-block align-text-top"> Gestión de Denuncias Ciudadanas</a>
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
                                <a class="nav-link" href="{{ route('coordinator.collection-process') }}"><i
                                        class="bi bi-collection"></i> Proceso de Recolección</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page"
                                    href="{{ route('coordinator.incidents') }}"><i
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

    <div class="body-container">
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

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h1 class="h3 mb-0"><i class="bi bi-exclamation-triangle"></i> Denuncias ciudadanas</h1>
                <span class="badge text-bg-dark">Total: {{ ($denuncias ?? collect())->count() }}</span>
            </div>

            @php
                $listaDenuncias = $denuncias ?? collect();
                $sizeCatalog = [
                    'pequeno' => 'Pequeño',
                    'mediano' => 'Mediano',
                    'grande' => 'Grande',
                ];

                $statusCatalog = $estadosDenuncia ?? [
                    'recibida' => 'Recibida',
                    'en_revision' => 'En revisión',
                    'asignada' => 'Asignada',
                    'en_atencion' => 'En atención',
                    'atendida' => 'Atendida',
                    'cerrada' => 'Cerrada',
                ];

                $normalizeSizeKey = function ($value): string {
                    $normalized = mb_strtolower(trim((string) $value), 'UTF-8');

                    return match ($normalized) {
                        'pequeno', 'pequeño' => 'pequeno',
                        'mediano' => 'mediano',
                        'grande' => 'grande',
                        default => $normalized !== '' ? str_replace(' ', '_', $normalized) : 'pequeno',
                    };
                };

                $normalizeStatusKey = function ($value): string {
                    $normalized = mb_strtolower(trim((string) $value), 'UTF-8');

                    return match ($normalized) {
                        'recibida' => 'recibida',
                        'asignada', 'asingada' => 'asignada',
                        'en revision', 'en_revision' => 'en_revision',
                        'en atencion', 'en_atencion', 'en atención' => 'en_atencion',
                        'atendida' => 'atendida',
                        'cerrada' => 'cerrada',
                        default => $normalized !== '' ? str_replace(' ', '_', $normalized) : 'recibida',
                    };
                };
            @endphp

            <section class="card mb-3">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-lg-6">
                            <label for="incidentSearch" class="form-label mb-1">Buscar denuncia</label>
                            <input id="incidentSearch" type="text" class="form-control"
                                placeholder="Buscar por ID, denunciante, telefono, email o descripcion">
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="statusFilter" class="form-label mb-1">Estado</label>
                            <select id="statusFilter" class="form-select">
                                <option value="">Todos</option>
                                <option value="recibida">Recibida</option>
                                <option value="en_revision">En revisión</option>
                                <option value="asignada">Asignada</option>
                                <option value="en_atencion">En atención</option>
                                <option value="atendida">Atendida</option>
                                <option value="cerrada">Cerrada</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="sizeFilter" class="form-label mb-1">Tamaño</label>
                            <select id="sizeFilter" class="form-select">
                                <option value="">Todos</option>
                                <option value="pequeno">Pequeño</option>
                                <option value="mediano">Mediano</option>
                                <option value="grande">Grande</option>
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            <div class="table-responsive">
                <table class="table table-striped table-hover" id="incidentsTable">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Denunciante</th>
                            <th>Contacto</th>
                            <th>Descripción</th>
                            <th>Tamaño</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($listaDenuncias as $denuncia)
                            @php
                                $estadoKey = $normalizeStatusKey($denuncia->estado);
                                $tamanoKey = $normalizeSizeKey($denuncia->tamano);
                                $estado = $statusCatalog[$estadoKey] ?? 'Recibida';
                                $tamano = $sizeCatalog[$tamanoKey] ?? 'Pequeño';

                                $estadoClass = match ($estadoKey) {
                                    'recibida' => 'text-bg-secondary',
                                    'asignada' => 'text-bg-dark',
                                    'en_revision' => 'text-bg-warning',
                                    'en_atencion' => 'text-bg-primary',
                                    'atendida' => 'text-bg-success',
                                    'cerrada' => 'text-bg-success',
                                    default => 'text-bg-secondary',
                                };

                                $descripcionCompleta = trim((string) ($denuncia->descripcion ?? ''));
                                $ubicacion = 'Sin ubicacion';
                                $descripcion = $descripcionCompleta !== '' ? $descripcionCompleta : 'Sin descripcion';

                                if (str_contains($descripcionCompleta, ' | Ubicacion: ')) {
                                    [$descripcion, $ubicacion] = explode(' | Ubicacion: ', $descripcionCompleta, 2);
                                }

                                $fotoUrl = '';

                                if (!empty($denuncia->foto)) {
                                    $foto = (string) $denuncia->foto;
                                    $fotoUrl = str_starts_with($foto, 'http://') || str_starts_with($foto, 'https://')
                                        ? $foto
                                        : asset('storage/' . ltrim($foto, '/'));
                                }
                            @endphp
                            <tr data-status="{{ $estadoKey }}" data-size="{{ $tamanoKey }}">
                                <td>{{ $denuncia->id_denuncia }}</td>
                                <td>{{ $denuncia->nombre_denunciante ?: 'Sin nombre' }}</td>
                                <td>
                                    <div>{{ $denuncia->telefono ?: 'Sin telefono' }}</div>
                                    <small class="text-muted">{{ $denuncia->email ?: 'Sin email' }}</small>
                                </td>
                                <td>{{ $descripcion }}</td>
                                <td>{{ $tamano }}</td>
                                <td><span class="badge {{ $estadoClass }}">{{ $estado }}</span></td>
                                <td>{{ optional($denuncia->fecha)->format('d/m/Y H:i') ?: 'Sin fecha' }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                        data-bs-target="#incidentDetailModal" data-incident-id="{{ $denuncia->id_denuncia }}"
                                        data-incident-name="{{ $denuncia->nombre_denunciante ?: 'Sin nombre' }}"
                                        data-incident-phone="{{ $denuncia->telefono ?: 'Sin telefono' }}"
                                        data-incident-email="{{ $denuncia->email ?: 'Sin email' }}"
                                        data-incident-description="{{ $descripcion }}"
                                        data-incident-size="{{ $tamano }}"
                                        data-incident-size-key="{{ $tamanoKey }}"
                                        data-incident-status="{{ $estado }}"
                                        data-incident-status-key="{{ $estadoKey }}"
                                        data-incident-date="{{ optional($denuncia->fecha)->format('d/m/Y H:i') ?: 'Sin fecha' }}"
                                        data-incident-location="{{ $ubicacion }}" data-incident-lat="{{ $denuncia->latitud }}"
                                        data-incident-lng="{{ $denuncia->longitud }}" data-incident-photo="{{ $fotoUrl }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p id="incidentsEmptyState" class="alert alert-light border d-none mb-0">No hay denuncias que coincidan con los filtros seleccionados.</p>
            </div>
        </div>
    </div>

    <div class="modal fade" id="incidentDetailModal" tabindex="-1" aria-labelledby="incidentDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="incidentDetailModalLabel">
                        <i class="bi bi-exclamation-triangle"></i> Detalle de denuncia
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>ID:</strong> #<span id="modalIncidentId">-</span></p>
                            <p class="mb-2"><strong>Denunciante:</strong> <span id="modalIncidentName">Sin nombre</span></p>
                            <p class="mb-2"><strong>Telefono:</strong> <span id="modalIncidentPhone">Sin telefono</span></p>
                            <p class="mb-2"><strong>Email:</strong> <span id="modalIncidentEmail">Sin email</span></p>
                            <p class="mb-2"><strong>Fecha:</strong> <span id="modalIncidentDate">Sin fecha</span></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Ubicacion:</strong> <span id="modalIncidentLocation">Sin ubicacion</span></p>
                            <p class="mb-2"><strong>Tamano:</strong> <span id="modalIncidentSize">Sin definir</span></p>
                            <p class="mb-2"><strong>Estado actual:</strong> <span id="modalIncidentCurrentStatus">Sin estado</span></p>
                            <p class="mb-0"><strong>Descripcion:</strong> <span id="modalIncidentDescription">Sin descripcion</span></p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-lg-5">
                            <h6>Fotografia</h6>
                            <img src="https://via.placeholder.com/640x360?text=Sin+foto" class="img-fluid rounded border"
                                id="modalIncidentPhoto" alt="Fotografia de denuncia">
                        </div>
                        <div class="col-lg-7">
                            <h6>Ubicacion en mapa</h6>
                            <div id="incidentMap" class="border rounded" style="height: 320px;"></div>
                            <small id="incidentMapMessage" class="text-muted d-block mt-2"></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <form method="POST" id="incidentStatusForm" action="#"
                        data-base-action="{{ url('/coordinator/incidents') }}">
                        @csrf
                        <div class="input-group">
                            <span class="input-group-text">Cambiar estado</span>
                            <select class="form-select" name="estado" id="modalIncidentStatusSelect" required>
                                @foreach (($statusCatalog ?? []) as $estadoValue => $estadoLabel)
                                    <option value="{{ $estadoValue }}">{{ $estadoLabel }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary">Guardar estado</button>
                        </div>
                    </form>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="{{ asset('js/coordinator/incidents.js') }}" defer></script>
</body>

</html>