<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('icono-reciclaje.png') }}">
    <title>Reportes y Estadísticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/admin/reports-stats.css') }}">
</head>

<body>
    @php
        $isAuditorView = ($viewScope ?? 'admin') === 'auditor';
        $homeRoute = $isAuditorView ? route('home-auditor') : route('home-admin');
        $reportsRoute = $isAuditorView ? route('auditor.reports') : route('admin.reports');
        $profileRoute = $isAuditorView ? route('profile-auditor') : route('admin.user-profile');
        $exportRoute = $isAuditorView ? route('auditor.reports.export') : route('admin.reports.export');
    @endphp

    <div class="navbar-container">
        <nav class="navbar bg-body-tertiary fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">
                    <img src="{{ asset('icono-reciclaje.png') }}" alt="Logo" width="30" height="24"
                        class="d-inline-block align-text-top">
                    Reportes y Estadísticas
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
                                <a class="nav-link" href="{{ $homeRoute }}"><i class="bi bi-house"></i> Inicio</a>
                            </li>
                            @unless($isAuditorView)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.users') }}"><i class="bi bi-file-earmark-medical"></i> Gestión de Usuarios</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.system-settings') }}"><i class="bi bi-gear"></i>
                                    Configuración del Sistema</a>
                            </li>
                            @endunless
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="{{ $reportsRoute }}"><i class="bi bi-bar-chart"></i>
                                    Reportes y Estadísticas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ $profileRoute }}"><i class="bi bi-person-circle"></i> Mi perfil</a>
                            </li>
                            @unless($isAuditorView)
                            <li>
                                <hr class="dropdown-divider my-1">
                            </li>
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
                            @endunless
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
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h4 class="mb-0 fw-bold"><i class="bi bi-bar-chart-line-fill text-success me-2"></i>Reportes Estratégicos</h4>
            <div class="d-flex align-items-center gap-2">
                <span class="small-muted">Corte: {{ \Carbon\Carbon::parse($fechaCorte)->format('d/m/Y H:i') }}</span>
                <a href="{{ $exportRoute }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-download me-1"></i>Exportar CSV
                </a>
            </div>
        </div>

        <div class="alert alert-light border">
            <div class="fw-semibold">Módulo de reportes en modo solo lectura</div>
            <div class="small-muted mb-0">Visualización y exportación de estadísticas sobre recolección, reciclaje y denuncias.</div>
        </div>

        <ul class="nav nav-tabs" id="reportsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-recoleccion" data-bs-toggle="tab" data-bs-target="#pane-recoleccion"
                    type="button" role="tab">
                    <i class="bi bi-truck me-1"></i>Recolección
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-reciclaje" data-bs-toggle="tab" data-bs-target="#pane-reciclaje"
                    type="button" role="tab">
                    <i class="bi bi-recycle me-1"></i>Reciclaje
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-denuncias" data-bs-toggle="tab" data-bs-target="#pane-denuncias"
                    type="button" role="tab">
                    <i class="bi bi-exclamation-triangle me-1"></i>Denuncias
                </button>
            </li>
        </ul>

        <div class="tab-content border border-top-0 rounded-bottom p-4 bg-white shadow-sm">
            <div class="tab-pane fade show active" id="pane-recoleccion" role="tabpanel">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card metric-card h-100">
                            <div class="card-body">
                                <div class="small-muted">Toneladas hoy</div>
                                <div class="h3 mb-0">{{ number_format($kpisRecoleccion['hoy'], 2) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card metric-card h-100">
                            <div class="card-body">
                                <div class="small-muted">Toneladas semana actual</div>
                                <div class="h3 mb-0">{{ number_format($kpisRecoleccion['semana'], 2) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card metric-card h-100">
                            <div class="card-body">
                                <div class="small-muted">Toneladas mes actual</div>
                                <div class="h3 mb-0">{{ number_format($kpisRecoleccion['mes'], 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header fw-semibold">Comparativa mensual (últimos 12 meses)</div>
                            <div class="card-body"><canvas id="chartRecoleccionMensual" height="110"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header fw-semibold">Comparativa anual</div>
                            <div class="card-body"><canvas id="chartRecoleccionAnual" height="110"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header fw-semibold">Toneladas por zona/colonia</div>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Zona</th>
                                            <th class="text-end">Toneladas</th>
                                            <th class="text-end">Viajes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($toneladasPorZona as $row)
                                            <tr>
                                                <td>{{ $row->zona }}</td>
                                                <td class="text-end">{{ number_format((float) $row->toneladas, 2) }}</td>
                                                <td class="text-end">{{ $row->viajes }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center text-muted py-3">Sin datos disponibles.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header fw-semibold">Toneladas por ruta específica</div>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Ruta</th>
                                            <th class="text-end">Toneladas</th>
                                            <th class="text-end">Ejecuciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($toneladasPorRuta as $row)
                                            <tr>
                                                <td>{{ $row->ruta }}</td>
                                                <td class="text-end">{{ number_format((float) $row->toneladas, 2) }}</td>
                                                <td class="text-end">{{ $row->ejecuciones }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center text-muted py-3">Sin datos disponibles.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="pane-reciclaje" role="tabpanel">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header fw-semibold">Cantidad reciclada por tipo de material</div>
                            <div class="card-body"><canvas id="chartMateriales" height="120"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header fw-semibold">Tendencias de reciclaje ciudadano</div>
                            <div class="card-body"><canvas id="chartTendenciaCiudadana" height="120"></canvas></div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header fw-semibold">Puntos verdes más activos</div>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Punto Verde</th>
                                            <th class="text-end">Kg reciclados</th>
                                            <th class="text-end">Entregas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($puntosMasActivos as $row)
                                            <tr>
                                                <td>{{ $row->punto_verde }}</td>
                                                <td class="text-end">{{ number_format((float) $row->kg_total, 2) }}</td>
                                                <td class="text-end">{{ $row->entregas }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center text-muted py-3">Sin datos disponibles.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header fw-semibold">Comparativa entre materiales</div>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Material</th>
                                            <th class="text-end">Kg</th>
                                            <th class="text-end">Participación</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($comparativaMateriales as $row)
                                            <tr>
                                                <td>{{ $row['material'] }}</td>
                                                <td class="text-end">{{ number_format((float) $row['kg_total'], 2) }}</td>
                                                <td class="text-end">{{ number_format((float) $row['porcentaje'], 2) }}%</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center text-muted py-3">Sin datos disponibles.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="pane-denuncias" role="tabpanel">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card metric-card h-100">
                            <div class="card-body">
                                <div class="small-muted">Denuncias totales</div>
                                <div class="h3 mb-0">{{ $denunciasResumen['total'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card metric-card h-100">
                            <div class="card-body">
                                <div class="small-muted">Atendidas / Cerradas</div>
                                <div class="h3 mb-0">{{ $denunciasResumen['atendidas'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card metric-card h-100">
                            <div class="card-body">
                                <div class="small-muted">Pendientes</div>
                                <div class="h3 mb-0">{{ $denunciasResumen['pendientes'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header fw-semibold">Denuncias por estado</div>
                            <div class="card-body"><canvas id="chartDenunciasEstado" height="120"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header fw-semibold">Tiempo promedio de atención</div>
                            <div class="card-body d-flex flex-column justify-content-center">
                                <div class="display-6 mb-1">{{ number_format($denunciasResumen['promedio_horas'], 2) }} h</div>
                                <div class="small-muted">Equivale a {{ number_format($denunciasResumen['promedio_dias'], 2) }} días promedio entre creación y estado atendida/cerrada.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header fw-semibold">Zonas con mayor cantidad de denuncias</div>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Zona</th>
                                            <th class="text-end">Denuncias</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($zonasMasDenuncias as $row)
                                            <tr>
                                                <td>{{ $row['zona'] }}</td>
                                                <td class="text-end">{{ $row['total'] }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="2" class="text-center text-muted py-3">Sin datos geográficos suficientes para estimar zonas.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.reportsData = {
            recoleccionMensual: {
                labels: @json($recoleccionMensual->pluck('periodo')->values()),
                data: @json($recoleccionMensual->map(fn($r) => round((float) $r->toneladas, 2))->values()),
            },
            recoleccionAnual: {
                labels: @json($recoleccionAnual->pluck('anio')->values()),
                data: @json($recoleccionAnual->map(fn($r) => round((float) $r->toneladas, 2))->values()),
            },
            materiales: {
                labels: @json($reciclajePorMaterial->pluck('material')->values()),
                data: @json($reciclajePorMaterial->map(fn($r) => round((float) $r->kg_total, 2))->values()),
            },
            tendenciaCiudadana: {
                labels: @json($tendenciaCiudadana->pluck('periodo')->values()),
                kg: @json($tendenciaCiudadana->map(fn($r) => round((float) $r->kg_total, 2))->values()),
                ciudadanos: @json($tendenciaCiudadana->pluck('ciudadanos_activos')->values()),
            },
            denunciasEstado: {
                labels: @json($denunciasPorEstado->pluck('estado')->values()),
                data: @json($denunciasPorEstado->pluck('total')->values()),
            },
        };
    </script>
    <script src="{{ asset('js/admin/reports-stats.js') }}"></script>
</body>

</html>