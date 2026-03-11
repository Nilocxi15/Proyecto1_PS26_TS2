<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('icono-reciclaje.png') }}">
    <link rel="stylesheet" href="{{ asset('css/citizen/reports.css') }}">
    <title>Reportes y Seguimiento</title>
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
    @endphp

    <div class="navbar-container">
        <nav class="navbar bg-body-tertiary fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="#"><img src="{{ asset('icono-reciclaje.png') }}" alt="Logo" width="30"
                    height="24" class="d-inline-block align-text-top"> Reportes y Seguimiento</a>
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
                                <a class="nav-link" href="{{ route('home-public') }}"><i class="bi bi-house"></i>
                                    Inicio</a>
                            </li>
                            <li class="nav-item">
                                @if ($estaAutenticado)
                                    <a class="nav-link active" aria-current="page" href="{{ route('report-citizen') }}"><i
                                            class="bi bi-file-earmark-medical"></i> Reportes y denuncias</a>
                                @else
                                    <a class="nav-link" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right"></i>
                                        Iniciar sesión para denunciar</a>
                                @endif
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('citizen.public-statistics') }}"><i
                                        class="bi bi-bar-chart"></i> Estadísticas
                                    Públicas</a>
                            </li>
                            @if ($estaAutenticado)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('profile-citizen') }}"><i
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
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <!-- Botón para crear nuevo reporte -->
            <div class="row mb-4">
                <div class="col-12">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#staticBackdrop">
                        <i class="bi bi-plus-circle"></i> Nuevo Reporte
                    </button>
                </div>
            </div>

            <!-- Tabla de historial de reportes -->
            <div class="row">
                <div class="col-12">
                    <div class="reports-section">
                        <h2><i class="bi bi-list-check"></i> Seguimiento de Denuncias Propias</h2>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th><i class="bi bi-calendar"></i> Fecha</th>
                                        <th><i class="bi bi-geo-alt"></i> Ubicación</th>
                                        <th><i class="bi bi-justify"></i> Descripción</th>
                                        <th><i class="bi bi-inbox"></i> Tamaño</th>
                                        <th><i class="bi bi-info-circle"></i> Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse (($denuncias ?? collect()) as $denuncia)
                                        @php
                                            $estado = $denuncia->estado ?? 'Recibida';
                                            $badgeEstado = match ($estado) {
                                                'Atendida' => 'bg-success',
                                                'En atencion', 'En atención' => 'bg-info',
                                                'Rechazada' => 'bg-danger',
                                                default => 'bg-secondary',
                                            };
                                            $badgeTamano = match ($denuncia->tamano) {
                                                'Grande' => 'bg-warning',
                                                'Mediano' => 'bg-primary',
                                                default => 'bg-success',
                                            };
                                            $ubicacion = 'Sin ubicación';
                                            $descripcion = $denuncia->descripcion ?? 'Sin descripción';

                                            if (str_contains($descripcion, ' | Ubicacion: ')) {
                                                [$descripcion, $ubicacion] = explode(' | Ubicacion: ', $descripcion, 2);
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $denuncia->id_denuncia }}</td>
                                            <td>{{ optional($denuncia->fecha)->format('d/m/Y') }}</td>
                                            <td>{{ $ubicacion }}</td>
                                            <td>{{ $descripcion }}</td>
                                            <td><span class="badge {{ $badgeTamano }}">{{ $denuncia->tamano }}</span></td>
                                            <td><span class="badge {{ $badgeEstado }}">{{ $estado }}</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                    data-bs-target="#viewReportModal">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">Aún no has registrado denuncias.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Agregar Nueva Denuncia -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Reportar Basurero Clandestino</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('report-citizen.store') }}" enctype="multipart/form-data">
                    @csrf
                <div class="modal-body">

                    <div class="input-group mb-3">
                        <span class="input-group-text" id="dumpLocation">Ubicación del Basurero</span>
                        <input type="text" class="form-control" name="ubicacion" placeholder="Zona, Avenida, Calle" aria-label="Ubicación"
                            aria-describedby="dumpLocation" value="{{ old('ubicacion') }}" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="latitud" class="form-label">Latitud</label>
                            <input type="text" class="form-control" id="latitud" name="latitud" value="{{ old('latitud') }}"
                                placeholder="Ej. 14.6349" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="longitud" class="form-label">Longitud</label>
                            <input type="text" class="form-control" id="longitud" name="longitud" value="{{ old('longitud') }}"
                                placeholder="Ej. -90.5069" readonly>
                        </div>
                    </div>

                    <div class="map-tools mb-3">
                        <label for="reportAddressSearch" class="form-label">Selecciona ubicación en el mapa</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="bi bi-geo"></i></span>
                            <input type="text" class="form-control" id="reportAddressSearch"
                                placeholder="Buscar dirección...">
                            <button type="button" class="btn btn-primary" id="reportSearchBtn">Buscar</button>
                            <button type="button" class="btn btn-outline-primary" id="reportMyLocationBtn">Mi ubicación</button>
                        </div>
                        <small id="reportMapMessage" class="text-muted"></small>
                    </div>

                    <div class="report-map-wrapper mb-3">
                        <div id="reportMap"></div>
                    </div>

                    <div class="mb-3">
                        <label for="photoInput" class="form-label">Seleccionar foto del lugar</label>
                        <input type="file" class="form-control" id="photoInput" name="foto" accept="image/*">
                        <small class="text-muted d-block mt-2">Formatos permitidos: JPG, PNG (Máximo 5MB)</small>
                    </div>

                    <div class="input-group mb-3">
                        <span class="input-group-text">Descripción del problema</span>
                        <textarea class="form-control" aria-label="Descripción del problema" name="descripcion"
                            id="problemDescription" required>{{ old('descripcion') }}</textarea>
                    </div>


                    <h4>Tamaño estimado del basurero</h4>
                    <div class="btn-group-vertical d-grid" role="group" aria-label="Vertical radio toggle button group">
                        <input type="radio" class="btn-check" name="tamano" value="Pequeno" id="vbtn-radio1" autocomplete="off"
                            {{ old('tamano', 'Pequeno') === 'Pequeno' ? 'checked' : '' }}>
                        <label class="btn btn-outline-success" for="vbtn-radio1">Pequeño</label>
                        <input type="radio" class="btn-check" name="tamano" value="Mediano" id="vbtn-radio2" autocomplete="off"
                            {{ old('tamano') === 'Mediano' ? 'checked' : '' }}>
                        <label class="btn btn-outline-warning" for="vbtn-radio2">Mediano</label>
                        <input type="radio" class="btn-check" name="tamano" value="Grande" id="vbtn-radio3" autocomplete="off"
                            {{ old('tamano') === 'Grande' ? 'checked' : '' }}>
                        <label class="btn btn-outline-danger" for="vbtn-radio3">Grande</label>
                    </div>

                    <h4>Datos del denunciante</h4>

                    <div class="input-group mb-3">
                        <span class="input-group-text" id="userName">Nombre</span>
                        <input type="text" class="form-control" placeholder="Nombre completo" aria-label="Nombre"
                            aria-describedby="userName" value="{{ $usuario?->nombre }}" readonly>
                    </div>

                    <div class="input-group mb-3">
                        <span class="input-group-text" id="userPhone">Teléfono</span>
                        <input type="text" class="form-control" placeholder="Número de teléfono" aria-label="Teléfono"
                            aria-describedby="userPhone" value="{{ $usuario?->telefono }}" readonly>
                    </div>

                    <div class="input-group mb-3">
                        <span class="input-group-text" id="userEmail">Email</span>
                        <input type="email" class="form-control" placeholder="Dirección de correo electrónico"
                            aria-label="Email" aria-describedby="userEmail" value="{{ $usuario?->email }}" readonly>
                    </div>

                    <div class="input-group mb-3">
                        <span class="input-group-text" id="complaintDate">Fecha de la Denuncia</span>
                        <input type="date" class="form-control" name="fecha" aria-label="Fecha de denuncia"
                            aria-describedby="complaintDate" value="{{ old('fecha', now()->toDateString()) }}" required>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger mb-0">{{ $errors->first() }}</div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Ver Detalle de Denuncia -->
    <div class="modal fade" id="viewReportModal" tabindex="-1" aria-labelledby="viewReportLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewReportLabel">
                        <i class="bi bi-eye"></i> Detalle de Denuncia
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Ubicación:</strong> Zona 3, Calle 5</p>
                            <p><strong>Tamaño:</strong> <span class="badge bg-warning">Grande</span></p>
                            <p><strong>Descripción:</strong> Basurero con residuos peligrosos</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Estado:</strong> <span class="badge bg-success">Atendida</span></p>
                            <p><strong>Fecha:</strong> 15/02/2026</p>
                            <p><strong>ID:</strong> #DEN-001-2026</p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <h6>Foto del Lugar</h6>
                        <img src="https://via.placeholder.com/400x300" class="img-fluid rounded" alt="Foto del lugar">
                    </div>
                    <div class="mb-3">
                        <h6>Datos del Denunciante</h6>
                        <p><strong>Nombre:</strong> Juan Pérez</p>
                        <p><strong>Teléfono:</strong> +502 1234 5678</p>
                        <p><strong>Email:</strong> juan@example.com</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('staticBackdrop')).show();
            });
        </script>
    @endif

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="{{ asset('js/citizen/reports.js') }}"></script>

</body>

</html>