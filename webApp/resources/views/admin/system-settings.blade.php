<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('icono-reciclaje.png') }}">
    <title>Configuración del Sistema</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Leaflet -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="{{ asset('css/admin/system-settings.css') }}">
</head>

<body>
    <div class="navbar-container">
        <nav class="navbar bg-body-tertiary fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">
                    <img src="{{ asset('icono-reciclaje.png') }}" alt="Logo" width="30" height="24"
                        class="d-inline-block align-text-top">
                    Configuración del Sistema
                </a>
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
                                <a class="nav-link" href="{{ route('home-admin') }}"><i class="bi bi-house"></i> Inicio</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.users') }}"><i class="bi bi-file-earmark-medical"></i> Gestión de Usuarios</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="{{ route('admin.system-settings') }}"><i class="bi bi-gear"></i> Configuración del Sistema</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.reports') }}"><i class="bi bi-bar-chart"></i> Reportes y Estadísticas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.user-profile') }}"><i class="bi bi-person-circle"></i> Mi perfil</a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li class="ps-2 py-0"><small class="text-muted fw-semibold">Vistas de otros roles</small></li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('home-coordinator') }}"><i class="bi bi-map-fill"></i> Coordinador de Rutas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('operator.containers') }}"><i class="bi bi-recycle"></i> Operador Punto Verde</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('home-auditor') }}"><i class="bi bi-clipboard-data"></i> Auditor</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('home-citizen') }}"><i class="bi bi-people-fill"></i> Ciudadano</a>
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


    <div class="container mt-4">

        {{-- Flash messages --}}
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-1"></i> {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <h4 class="mb-3 fw-bold"><i class="bi bi-gear-fill text-success me-2"></i>Configuración del Sistema</h4>

        @php
            $activeTab = session('active_tab', 'roles');
        @endphp

        <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'roles' ? 'active' : '' }}" id="tab-roles"
                    data-bs-toggle="tab" data-bs-target="#pane-roles" type="button" role="tab">
                    <i class="bi bi-shield-lock me-1"></i>Roles
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'materiales' ? 'active' : '' }}" id="tab-materiales"
                    data-bs-toggle="tab" data-bs-target="#pane-materiales" type="button" role="tab">
                    <i class="bi bi-recycle me-1"></i>Tipos de Material
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'puntos' ? 'active' : '' }}" id="tab-puntos"
                    data-bs-toggle="tab" data-bs-target="#pane-puntos" type="button" role="tab">
                    <i class="bi bi-geo-alt-fill me-1"></i>Puntos Verdes
                </button>
            </li>
        </ul>

        <div class="tab-content border border-top-0 rounded-bottom p-4 bg-white shadow-sm">

            <div class="tab-pane fade {{ $activeTab === 'roles' ? 'show active' : '' }}" id="pane-roles" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-semibold">Gestión de Roles</h5>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreateRole">
                        <i class="bi bi-plus-circle me-1"></i>Nuevo Rol
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-vertical-align align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($roles as $role)
                                <tr>
                                    <td>{{ $role->id_role }}</td>
                                    <td>{{ $role->nombre }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-outline-primary btn-sm"
                                            onclick="openEditRole({{ $role->id_role }}, '{{ addslashes($role->nombre) }}')">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm"
                                            onclick="confirmDeleteRole({{ $role->id_role }}, '{{ addslashes($role->nombre) }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No hay roles registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'materiales' ? 'show active' : '' }}" id="pane-materiales" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-semibold">Tipos de Material</h5>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreateMaterial">
                        <i class="bi bi-plus-circle me-1"></i>Nuevo Tipo
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-vertical-align align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($materiales as $material)
                                <tr>
                                    <td>{{ $material->id_material }}</td>
                                    <td>{{ $material->nombre }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-outline-primary btn-sm"
                                            onclick="openEditMaterial({{ $material->id_material }}, '{{ addslashes($material->nombre) }}')">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm"
                                            onclick="confirmDeleteMaterial({{ $material->id_material }}, '{{ addslashes($material->nombre) }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No hay tipos de material registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'puntos' ? 'show active' : '' }}" id="pane-puntos" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-semibold">Puntos Verdes de Reciclaje</h5>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreatePunto">
                        <i class="bi bi-plus-circle me-1"></i>Nuevo Punto Verde
                    </button>
                </div>

                <div id="map-overview" class="mb-4"></div>

                <div class="input-group mb-3" style="max-width:360px;">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="filterPuntos" class="form-control" placeholder="Buscar por nombre o dirección…">
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-vertical-align align-middle" id="tablePuntos">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Dirección</th>
                                <th>Capacidad (m³)</th>
                                <th>Horario</th>
                                <th>Encargado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablePuntosBody">
                            @forelse ($puntosVerdes as $punto)
                                <tr>
                                    <td>{{ $punto->id_punto_verde }}</td>
                                    <td>{{ $punto->nombre }}</td>
                                    <td>{{ $punto->direccion }}</td>
                                    <td>{{ number_format($punto->capacidad_m3, 2) }}</td>
                                    <td>{{ $punto->horario ?? '—' }}</td>
                                    <td>{{ $punto->encargado?->nombre ?? '—' }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-outline-primary btn-sm" onclick="openEditPunto({
                                            id: {{ $punto->id_punto_verde }},
                                            nombre: '{{ addslashes($punto->nombre) }}',
                                            direccion: '{{ addslashes($punto->direccion) }}',
                                            latitud: {{ $punto->latitud }},
                                            longitud: {{ $punto->longitud }},
                                            capacidad_m3: '{{ $punto->capacidad_m3 }}',
                                            horario: '{{ addslashes($punto->horario ?? '') }}',
                                            id_encargado: {{ $punto->id_encargado ?? 'null' }}
                                        })">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm"
                                            onclick="confirmDeletePunto({{ $punto->id_punto_verde }}, '{{ addslashes($punto->nombre) }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No hay puntos verdes registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>


    <div class="modal fade" id="modalCreateRole" tabindex="-1" aria-labelledby="modalCreateRoleLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('admin.settings.roles.store') }}">
                @csrf
                <input type="hidden" name="_tab" value="roles">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCreateRoleLabel"><i class="bi bi-shield-plus me-1"></i>Nuevo Rol</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nombre del Rol <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" maxlength="60" required
                                placeholder="Ej. Supervisor">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalEditRole" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="formEditRole">
                @csrf
                @method('PUT')
                <input type="hidden" name="_tab" value="roles">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil-square me-1"></i>Editar Rol</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nombre del Rol <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="editRoleNombre" class="form-control" maxlength="60" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalDeleteRole" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <form method="POST" id="formDeleteRole">
                @csrf
                @method('DELETE')
                <input type="hidden" name="_tab" value="roles">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>Eliminar Rol</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-0">
                        <p>¿Eliminar el rol <strong id="deleteRoleName"></strong>?</p>
                        <p class="text-muted small">Si tiene usuarios asignados, no se podrá eliminar.</p>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalCreateMaterial" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('admin.settings.materiales.store') }}">
                @csrf
                <input type="hidden" name="_tab" value="materiales">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-plus-circle me-1"></i>Nuevo Tipo de Material</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" maxlength="80" required
                                placeholder="Ej. Vidrio">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalEditMaterial" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="formEditMaterial">
                @csrf
                @method('PUT')
                <input type="hidden" name="_tab" value="materiales">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil-square me-1"></i>Editar Tipo de Material</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="editMaterialNombre" class="form-control" maxlength="80" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalDeleteMaterial" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <form method="POST" id="formDeleteMaterial">
                @csrf
                @method('DELETE')
                <input type="hidden" name="_tab" value="materiales">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>Eliminar Tipo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-0">
                        <p>¿Eliminar el tipo <strong id="deleteMaterialName"></strong>?</p>
                        <p class="text-muted small">Si tiene contenedores asociados, no se podrá eliminar.</p>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalCreatePunto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form method="POST" action="{{ route('admin.settings.puntos.store') }}">
                @csrf
                <input type="hidden" name="_tab" value="puntos">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-geo-alt-fill text-success me-1"></i>Nuevo Punto Verde</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" class="form-control" maxlength="120" required
                                    placeholder="Ej. Punto Verde Zona 1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Dirección <span class="text-danger">*</span></label>
                                <input type="text" name="direccion" class="form-control" maxlength="255" required
                                    placeholder="Ej. 10 Calle 5-20, Zona 1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Capacidad (m³) <span class="text-danger">*</span></label>
                                <input type="number" name="capacidad_m3" class="form-control" step="0.01" min="0.01" required
                                    placeholder="Ej. 5.00">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Horario</label>
                                <input type="text" name="horario" class="form-control" maxlength="120"
                                    placeholder="Ej. Lun-Vie 8:00-18:00">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Encargado (Operador de Punto Verde)</label>
                                <select name="id_encargado" class="form-select">
                                    <option value="">— Sin encargado —</option>
                                    @foreach ($operadores as $op)
                                        <option value="{{ $op->id_usuario }}">{{ $op->nombre }} ({{ $op->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Latitud <span class="text-danger">*</span></label>
                                <input type="number" name="latitud" id="createLatitud" class="form-control"
                                    step="0.0000001" min="-90" max="90" required readonly
                                    placeholder="Selecciona en el mapa">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Longitud <span class="text-danger">*</span></label>
                                <input type="number" name="longitud" id="createLongitud" class="form-control"
                                    step="0.0000001" min="-180" max="180" required readonly
                                    placeholder="Selecciona en el mapa">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold text-muted small">
                                    <i class="bi bi-info-circle me-1"></i>Haz clic en el mapa para seleccionar la ubicación.
                                </label>
                                <div id="map-create"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalEditPunto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form method="POST" id="formEditPunto">
                @csrf
                @method('PUT')
                <input type="hidden" name="_tab" value="puntos">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil-square me-1"></i>Editar Punto Verde</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" id="editPuntoNombre" class="form-control" maxlength="120" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Dirección <span class="text-danger">*</span></label>
                                <input type="text" name="direccion" id="editPuntoDireccion" class="form-control" maxlength="255" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Capacidad (m³) <span class="text-danger">*</span></label>
                                <input type="number" name="capacidad_m3" id="editPuntoCapacidad" class="form-control" step="0.01" min="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Horario</label>
                                <input type="text" name="horario" id="editPuntoHorario" class="form-control" maxlength="120">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Encargado (Operador de Punto Verde)</label>
                                <select name="id_encargado" id="editPuntoEncargado" class="form-select">
                                    <option value="">— Sin encargado —</option>
                                    @foreach ($operadores as $op)
                                        <option value="{{ $op->id_usuario }}">{{ $op->nombre }} ({{ $op->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Latitud <span class="text-danger">*</span></label>
                                <input type="number" name="latitud" id="editLatitud" class="form-control"
                                    step="0.0000001" min="-90" max="90" required readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Longitud <span class="text-danger">*</span></label>
                                <input type="number" name="longitud" id="editLongitud" class="form-control"
                                    step="0.0000001" min="-180" max="180" required readonly>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold text-muted small">
                                    <i class="bi bi-info-circle me-1"></i>Haz clic en el mapa para mover la ubicación.
                                </label>
                                <div id="map-edit"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalDeletePunto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <form method="POST" id="formDeletePunto">
                @csrf
                @method('DELETE')
                <input type="hidden" name="_tab" value="puntos">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>Eliminar Punto Verde</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-0">
                        <p>¿Eliminar <strong id="deletePuntoName"></strong>?</p>
                        <p class="text-muted small">Si tiene contenedores asociados, no se podrá eliminar.</p>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>

    @php
        $puntosBootstrap = $puntosVerdes->map(function ($p) {
            return [
                'lat' => (float) $p->latitud,
                'lng' => (float) $p->longitud,
                'nombre' => $p->nombre,
                'dir' => $p->direccion,
                'cap' => $p->capacidad_m3,
                'horario' => $p->horario ?? '—',
                'enc' => optional($p->encargado)->nombre ?? '—',
            ];
        })->values();
    @endphp

    <div id="system-settings-bootstrap" hidden
        data-active-tab="{{ $activeTab }}"
        data-role-base="{{ url('/admin/system-settings/roles') }}"
        data-material-base="{{ url('/admin/system-settings/materiales') }}"
        data-punto-base="{{ url('/admin/system-settings/puntos-verdes') }}"
        data-puntos='@json($puntosBootstrap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)'>
    </div>
    <script src="{{ asset('js/admin/system-settings.js') }}"></script>
</body>

</html>
